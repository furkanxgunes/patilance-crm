<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppWebhookController extends Controller
{
    // .env: WHATSAPP_VERIFY_TOKEN=patilance123
    // .env: WHATSAPP_APP_SECRET=... (opsiyonel, imza doğrulamak için)

    public function handle(Request $request)
    {
        // 1) VERIFY (GET)
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
            $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
            $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

            if ($mode === 'subscribe' && $token === env('WHATSAPP_VERIFY_TOKEN')) {
                return response($challenge, 200);
            }
            return response('Forbidden', 403);
        }

        // 2) OPTIONALLY: X-Hub-Signature-256 doğrulaması (tavsiye edilir)
        // Meta dokümantasyonu: isteğin gövdesi HMAC-SHA256 ile app secret üzerinden imzalanır.
        // İmza yanlışsa işlemeden 401 dönebilirsin.
        $appSecret = env('WHATSAPP_APP_SECRET');
        if ($appSecret) {
            $signature = $request->header('X-Hub-Signature-256');
            if (!$this->isValidSignature($request->getContent(), $signature, $appSecret)) {
                Log::warning('WhatsApp webhook invalid signature');
                return response('Invalid signature', 401);
            }
        }

        // 3) PAYLOAD PARSE
        $payload = $request->json()->all();
        // Hızlı “heartbeat”/basic log:
        $object = $payload['object'] ?? null; // genelde "whatsapp_business_account"
        $entries = $payload['entry'] ?? [];

        if (!$object || empty($entries)) {
            $this->logWa('unknown', $payload, 'Payload object/entry missing');
            return response('ok', 200);
        }

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                $value = $change['value'] ?? [];
                $field = $change['field'] ?? null; // "messages" beklenir

                // 3a) INBOUND MESSAGES
                if (!empty($value['messages'])) {
                    foreach ($value['messages'] as $msg) {
                        $msgType = $msg['type'] ?? 'unknown';
                        $from = $msg['from'] ?? null;
                        $wamid = $msg['id'] ?? null;

                        // text, image, document, audio vb. türleri kapsa
                        $body = null;
                        if ($msgType === 'text') {
                            $body = $msg['text']['body'] ?? null;
                        } elseif ($msgType === 'button') {
                            $body = $msg['button']['text'] ?? null;
                        } elseif ($msgType === 'interactive') {
                            $body = $msg['interactive']['button_reply']['title']
                                ?? $msg['interactive']['list_reply']['title']
                                ?? null;
                        } else {
                            // Diğer türler için ham JSON’u kaydediyoruz
                            $body = json_encode($msg);
                        }

                        $this->logWa('message_in', [
                            'from' => $from,
                            'wamid' => $wamid,
                            'type' => $msgType,
                            'body' => $body,
                            'metadata' => $value['metadata'] ?? [],
                        ], 'Inbound message');

                        // İstersen burada job dispatch edip cevaplama/kuyruk yap.
                    }
                }

                // 3b) STATUSES (delivered/read/failed vs.)
                if (!empty($value['statuses'])) {
                    foreach ($value['statuses'] as $status) {
                        $this->logWa('status', $status, 'Message status');
                    }
                }

                // 3c) ERRORS / EXCEPTIONS (nadir)
                if (!empty($value['errors'])) {
                    foreach (($value['errors'] ?? []) as $err) {
                        $this->logWa('error', $err, 'Webhook error');
                    }
                }

                // 3d) Hiçbiri değilse
                if (empty($value['messages']) && empty($value['statuses']) && empty($value['errors'])) {
                    $this->logWa('unknown', $value, 'No messages/statuses/errors in change');
                }
            }
        }

        return response('ok', 200);
    }

    private function logWa(string $kind, $data, string $note = null): void
    {
        try {
            DB::table('wa_message_logs')->insert([
                'kind'       => $kind,                               // message_in | status | error | unknown | heartbeat
                'note'       => $note,
                'payload'    => is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('wa_message_logs insert error: '.$e->getMessage());
        }
    }

    private function isValidSignature(string $rawBody, ?string $header, string $appSecret): bool
    {
        if (!$header || !str_starts_with($header, 'sha256=')) return false;
        $sig = substr($header, 7);
        $expected = hash_hmac('sha256', $rawBody, $appSecret);
        // Meta ve GitHub benzeri webhooks için yöntem aynı: HMAC SHA256, sabit zamanlı karşılaştırma iyi olur
        return hash_equals($expected, $sig);
    }
}
