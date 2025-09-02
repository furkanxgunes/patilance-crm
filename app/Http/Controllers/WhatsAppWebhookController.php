<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
// Modeli yukarıda use etmene gerek yok, tam nitelikli adla da çağırıyoruz.
class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('WA headers', $request->headers->all());
Log::info('WA raw', ['raw' => $request->getContent()]);
Log::info('WA parsed', $request->all());
 
        // --- GET: Meta doğrulama ---
        if ($request->isMethod('get')) {
            $mode      = $request->query('hub.mode', $request->query('hub_mode'));
            $token     = $request->query('hub.verify_token', $request->query('hub_verify_token'));
            $challenge = $request->query('hub.challenge', $request->query('hub_challenge'));

            if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }
            return response('Token mismatch', 403);
        }

        // --- POST: her seferinde DB'ye bir "heartbeat" düş (tetikleniyor mu görelim) ---
        try {
            \App\Models\WaMessageLog::create([
                'direction' => 'status',
                'status'    => 'raw_dump',
                'raw'       => json_decode($request->getContent(), true),
              ]);
              
            \App\Models\WaMessageLog::create([
                'direction'  => 'status',
                'status'     => 'heartbeat',
                'raw'        => ['received_at' => now()->toISOString()],
            ]);
        } catch (\Throwable $e) {
            // tablo yoksa/migration atılmadıysa 200 dönüp sessiz geç
        }

        try {
            // --- İmza doğrulama (opsiyonel) ---
            $sigHeader = $request->header('X-Hub-Signature-256');
            $appSecret = config('services.whatsapp.app_secret');

            if ($appSecret && $sigHeader) {
                $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret, false);
                if (!hash_equals($expected, $sigHeader)) {
                    // İmzayı doğrulayamadık; yine de 200 dönelim, ama DB'ye not düşelim.
                    \App\Models\WaMessageLog::create([
                        'direction' => 'status',
                        'status'    => 'signature_mismatch',
                        'raw'       => ['header' => $sigHeader],
                    ]);
                    return response('OK', 200);
                }
            }

            // --- Payload işleme ---
            $payload = $request->all();

            // WhatsApp tipik gövde: entry[0].changes[0].value
            $value = $payload['entry'][0]['changes'][0]['value'] ?? null;

            if (!$value) {
                \App\Models\WaMessageLog::create([
                    'direction' => 'status',
                    'status'    => 'unknown_payload',
                    'raw'       => $payload,
                ]);
                return response('OK', 200);
            }

            // 1) Gelen mesajlar (inbound)
            if (!empty($value['messages'])) {
                foreach ($value['messages'] as $msg) {
                    $type = $msg['type'] ?? null;
                    \App\Models\WaMessageLog::create([
                        'direction'  => 'inbound',
                        'wa_id'      => $msg['from'] ?? null,
                        'message_id' => $msg['id'] ?? null,
                        'type'       => $type,
                        'body'       => $type === 'text' ? ($msg['text']['body'] ?? null) : null,
                        'status'     => null,
                        'raw'        => $msg,
                    ]);
                }
            }

            // 2) Durumlar (sent/delivered/read/failed)
            if (!empty($value['statuses'])) {
                foreach ($value['statuses'] as $st) {
                    \App\Models\WaMessageLog::create([
                        'direction'  => 'status',
                        'wa_id'      => $st['recipient_id'] ?? null,
                        'message_id' => $st['id'] ?? null,
                        'type'       => 'status',
                        'status'     => $st['status'] ?? null,
                        'raw'        => $st,
                    ]);
                }
            }

            return response('OK', Response::HTTP_OK);
        } catch (\Throwable $e) {
            // Hiçbir durumda 500 dönmeyelim; hatayı DB'ye not düş.
            try {
                \App\Models\WaMessageLog::create([
                    'direction' => 'status',
                    'status'    => 'handler_error',
                    'raw'       => ['exception' => $e->getMessage()],
                ]);
            } catch (\Throwable $ignored) {}
            return response('OK', 200);
        }
    }
}
