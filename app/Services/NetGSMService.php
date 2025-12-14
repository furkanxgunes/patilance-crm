<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NetGSMService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('netgsm');
    }

   
public function sendSms(string $phone, string $message, ?string $waMessageId = null): array
{
    try {
        // SMS log kaydı oluştur
        $smsLog = SmsLog::create([
            'wa_message_id' => $waMessageId,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending'
        ]);

        // Telefon numarasını temizle
        $phone = $this->formatPhoneNumber($phone);

        // NetGSM API isteği için parametreler
        $params = [
            'usercode' => $this->config['username'],
            'password' => $this->config['password'],
            'gsmno' => $phone,
            'message' => $message,
            'msgheader' => $this->config['header'],
            'dil' => $this->config['language'],
            'filter' => 0,
            'startdate' => now()->format('dmYHi'),
            'stopdate' => now()->addDay()->format('dmYHi')
        ];

        // API'ye istek gönder
        $client = new \GuzzleHttp\Client();
        $response = $client->post($this->config['api_url'], [
            'form_params' => $params
        ]);
     
        $responseBody = $response->getBody()->getContents();
       
        // Başarılı yanıt kontrolü
        if (strpos($responseBody, '00') === 0 || strpos($responseBody, '01') === 0 || strpos($responseBody, '02') === 0) {
            $smsLog->update([
                'status' => 'sent',
                'response' => $responseBody
            ]);
            return ['success' => true, 'message' => 'SMS başarıyla gönderildi', 'knk' => 'burdayiiiiizz.!!'];
        }

        // Hata durumunda
        $errorMessage = $this->getErrorMessage($responseBody);
        $smsLog->update([
            'status' => 'failed',
            'response' => $errorMessage
        ]);
        
        return ['success' => false, 'message' => $errorMessage];

    } catch (\Exception $e) {
        \Log::error('NetGSM SMS gönderim hatası: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

    protected function formatPhoneNumber(string $phone): string
    {
        // Başındaki + işaretini kaldır
        $phone = ltrim($phone, '+');
        
        // 90 ile başlamıyorsa başına 90 ekle
        if (!str_starts_with($phone, '90')) {
            // Eğer 0 ile başlıyorsa 0'ı kaldır
            if (str_starts_with($phone, '0')) {
                $phone = '9' . substr($phone, 1);
            }
            $phone = '90' . ltrim($phone, '90');
        }
        
        return $phone;
    }

    protected function getErrorMessage( $code): string
    {
        // Eğer gelen kod string ise ve içinde sayısal bir değer varsa onu al
    if (is_string($code) && preg_match('/\d+/', $code, $matches)) {
        return 'Hata kodu:' . (string)$code;
    } elseif (!is_int($code)) {
        return 'Geçersiz hata kodu: ' . (string)$code;
    }
        

    }
}
