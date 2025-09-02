<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppWebhookController extends Controller
{
    // .env -> WHATSAPP_VERIFY_TOKEN=patilance123
    // .env -> WHATSAPP_APP_SECRET=... (Meta Uygulama Gizli Anahtarınız)
    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            $mode      = $request->query('hub.mode', $request->query('hub_mode'));
            $token     = $request->query('hub.verify_token', $request->query('hub_verify_token'));
            $challenge = $request->query('hub.challenge', $request->query('hub_challenge'));

            if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
                return response($challenge, 200)->header('Content-Type', 'text/plain');
            }
            return response('Token mismatch', 403);
        }

        // --- İmza doğrulama (opsiyonel ama önerilir) ---
        $sigHeader = $request->header('X-Hub-Signature-256');
        $appSecret = config('services.whatsapp.app_secret');
        if ($appSecret && $sigHeader) {
            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret, false);
            if (!hash_equals($expected, $sigHeader)) {
                Log::warning('WA Webhook: Signature mismatch');
                return response('Invalid signature', 403);
            }
        }

        // --- Payload işleme ---
        $payload = $request->all();
        Log::info('WA Webhook', ['payload' => $payload]);

        // WhatsApp Cloud API tipik gövde yapısı
        if (!empty($payload['entry'][0]['changes'][0]['value'])) {
            $value = $payload['entry'][0]['changes'][0]['value'];

            // 1) Gelen mesajlar (customers -> business)
            if (!empty($value['messages'])) {
                foreach ($value['messages'] as $msg) {
                    $from   = $msg['from'] ?? null; // wa_id (E.164)
                    $id     = $msg['id']   ?? null;
                    $type   = $msg['type'] ?? null;
                    $text   = $type === 'text' ? ($msg['text']['body'] ?? null) : null;

                    // DB’ye yaz (aşağıdaki migration/model ile)
                    \App\Models\WaMessageLog::create([
                        'direction' => 'inbound',
                        'wa_id'     => $from,
                        'message_id'=> $id,
                        'type'      => $type,
                        'body'      => $text,
                        'status'    => null,
                        'raw'       => $msg,
                    ]);
                }
            }

            // 2) Durum güncellemeleri (sent/delivered/read/failed)
            if (!empty($value['statuses'])) {
                foreach ($value['statuses'] as $st) {
                    $id     = $st['id']        ?? null; // message_id
                    $status = $st['status']    ?? null; // sent|delivered|read|failed
                    $to     = $st['recipient_id'] ?? null;

                    \App\Models\WaMessageLog::create([
                        'direction' => 'status',
                        'wa_id'     => $to,
                        'message_id'=> $id,
                        'type'      => 'status',
                        'body'      => null,
                        'status'    => $status,
                        'raw'       => $st,
                    ]);
                }
            }
        }

        // WhatsApp 200 OK bekler; ekstra çıktı yok.
        return response('OK', Response::HTTP_OK);
    }
}
