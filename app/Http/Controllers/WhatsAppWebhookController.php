<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\WaMessageLog;
// Modeli yukarıda use etmene gerek yok, tam nitelikli adla da çağırıyoruz.
class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 0) Görünür log (debug: sonra kısarsın)
        \Log::info('WA headers', $request->headers->all());
        $raw = $request->getContent();
        \Log::info('WA raw', ['raw' => $raw]);
    
        // 1) GET doğrulama (Meta Verify)
        if ($request->isMethod('get')) {
            $mode      = $request->query('hub.mode', $request->query('hub_mode'));
            $token     = $request->query('hub.verify_token', $request->query('hub_verify_token'));
            $challenge = $request->query('hub.challenge', $request->query('hub_challenge'));
    
            if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token') && $challenge) {
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }
            return response('Token mismatch', 403);
        }
    
        // 2) Payload parse (ham gövdeden zorla)
        $payload = $request->json()->all();
        if (!$payload) {
            $payload = json_decode($raw, true) ?: [];
        }
        \Log::info('WA parsed_all', $payload);
   
        // 4) (Opsiyonel) İmza doğrulama – ilk etapta kapalı tutmak istersen bu bloğu yoruma al
        $sigHeader = $request->header('X-Hub-Signature-256');
        $appSecret = config('services.whatsapp.app_secret');
        if ($appSecret && $sigHeader) {
            $expected = 'sha256=' . hash_hmac('sha256', $raw, $appSecret, false);
            if (!hash_equals($expected, $sigHeader)) {
                \App\Models\WaMessageLog::create([
                    'direction' => 'status',
                    'status'    => 'signature_mismatch',
                    'raw'       => ['header' => $sigHeader],
                ]);
                // 200 dön, Meta yeniden denemesin
                return response('OK', 200);
            }
        }
    
        // 5) WhatsApp body → entry[0].changes[0].value
        $value = $payload['entry'][0]['changes'][0]['value'] ?? null;
        if (!$value) {
            \App\Models\WaMessageLog::create([
                'direction' => 'status',
                'status'    => 'unknown_payload',
                'raw'       => $payload,
            ]);
            return response('OK', 200);
        }
    
        // 6) Gelen mesajlar (inbound)
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
    
        // 7) Durum bildirimleri (sent/delivered/read/failed)
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
    
        return response('OK', 200);
    }
    
}
