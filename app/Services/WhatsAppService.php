<?php

// app/Services/WhatsAppService.php

namespace App\Services;

use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $phoneNumberId;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.base_url');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }
    protected function uploadMedia(string $filePath, string $mime = 'application/pdf'): array
{
    try {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File not found: '.$filePath];
        }

        $url = "https://graph.facebook.com/v22.0/{$this->phoneNumberId}/media";

        // multipart/form-data (file upload)
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->asMultipart()->post($url, [
            [ 'name' => 'messaging_product', 'contents' => 'whatsapp' ],
            [ 'name' => 'type',              'contents' => $mime ],
            [ 'name' => 'file',              'contents' => fopen($filePath, 'r'), 'filename' => basename($filePath) ],
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['id'])) {
            return ['success' => true, 'id' => $data['id'], 'response' => $data];
        }

        \Log::error('WhatsApp media upload failed', ['status' => $response->status(), 'response' => $data]);
        return ['success' => false, 'error' => $data['error']['message'] ?? 'Upload failed', 'details' => $data];
    } catch (\Exception $e) {
        \Log::error('WhatsApp media upload exception', ['error' => $e->getMessage()]);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
public function sendMessageWithDocument(
    string $phone,
    string $caption,
    string $documentPath,
    string $templateName,
    array $bodyParams = []
): array {
    try {
        $phone = $this->formatPhoneNumber($phone);

        // 1) Önce PDF'i Meta'ya upload et
        $upload = $this->uploadMedia($documentPath, 'application/pdf');

        $components = [];

        if ($upload['success'] ?? false) {
            // Header -> Document by ID (en sağlam yöntem)
            $components[] = [
                'type' => 'header',
                'parameters' => [[
                    'type' => 'document',
                    'document' => [
                        'id' => $upload['id'],
                        // filename isteğe bağlı; template header paramda genelde gerekmez
                        'filename' => basename($documentPath),
                    ],
                ]],
            ];
        } else {
            // Fallback: public URL (mevcut yöntem)
            $documentUrl = $this->getTemporaryUrl($documentPath);
            $components[] = [
                'type' => 'header',
                'parameters' => [[
                    'type' => 'document',
                    'document' => [
                        'link' => $documentUrl,
                        'filename' => basename($documentPath),
                    ],
                ]],
            ];
        }

        // Body parametreleri
        if (!empty($bodyParams)) {
            $parameters = [];
            foreach ($bodyParams as $param) {
                if (is_string($param) && trim($param) !== '') {
                    $parameters[] = ['type' => 'text', 'text' => $param];
                }
            }
            if (!empty($parameters)) {
                $components[] = ['type' => 'body', 'parameters' => $parameters];
            }
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [ 'code' => 'en', 'policy' => 'deterministic' ],
                'components' => $components,
            ],
        ];

        $url = "https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages";

        \Log::debug('WhatsApp Document Template Request', ['url' => $url, 'payload' => $payload]);

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        $responseData = $response->json();

        if ($response->successful()) {
            return ['success' => true, 'response' => $responseData];
        }

        \Log::error('WhatsApp API Error', ['status' => $response->status(), 'response' => $responseData]);
        return [
            'success' => false,
            'error'   => $responseData['error']['message'] ?? 'Bilinmeyen hata',
            'details' => $responseData,
        ];
    } catch (\Exception $e) {
        \Log::error('WhatsApp document send error', ['phone' => $phone, 'caption' => $caption, 'error' => $e->getMessage()]);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
    
    public function sendMessage(string $to, string $message, string $templateName = null, array $templateParams = [])
    {
        Log::info('WhatsAppService: Mesaj gönderiliyor', [
            'to' => $to,
            'template' => $templateName,
            'message' => $message,
            'params' => $templateParams
        ]);

        try {
            $to = $this->formatPhoneNumber($to);
            
            if (!$to) {
                $error = 'Geçersiz telefon numarası';
                Log::error($error, ['original_number' => $to]);
                throw new \Exception($error);
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'en',
                        'policy' => 'deterministic'
                    ], 
                    'components' => []
                ]
            ]; 

            // Add body parameters if any
            if (!empty($templateParams)) {
                $parameters = [];
                foreach ($templateParams as $param) {
                    if (is_string($param) && trim($param) !== '') {
                        $parameters[] = ['type' => 'text', 'text' => $param];
                    }
                }
                
                if (!empty($parameters)) {
                    $payload['template']['components'][] = [
                        'type' => 'body',
                        'parameters' => $parameters
                    ];
                }
            }

            $url = "https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages";
            
            Log::debug('WhatsApp API İsteği', [
                'url' => $url,
                'payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $responseData = $response->json();
            
            Log::debug('WhatsApp API Yanıtı', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'] ?? null
                ];
            }

            Log::error('WhatsApp API Error', [
                'status' => $response->status(),
                'response' => $responseData
            ]);
            
            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Bilinmeyen hata',
                'details' => $responseData
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Error: ' . $e->getMessage());
            return false;
        }
    }

    protected function formatPhoneNumber(string $phone): string
    {
        // +905551234567 formatına çevir
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            return '90' . substr($phone, 1);
        }
        return $phone;
    }

    protected function prepareComponents(array $parameters): array
    {
        if (empty($parameters)) {
            return [];
        }

        return [
            [
                'type' => 'body',
                'parameters' => array_map(function ($value) {
                    return ['type' => 'text', 'text' => $value];
                }, $parameters)
            ]
        ];
    }
    /**
         * Storage içindeki dosya için geçici public URL döndürür
         */
        protected function getTemporaryUrl(string $path): string
        {
            if (!is_file($path)) {
                \Log::warning('getTemporaryUrl: file not found', ['path' => $path]);
            }
        
            $base = rtrim(config('app.url'), '/');
        
            $filename = rawurlencode(basename($path));
        
            return "{$base}/pdf/{$filename}";
        }

} 