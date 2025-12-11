<?php

namespace App\Console\Commands;

use App\Models\WaMessageLog;
use App\Models\WhatsAppMessage;
use App\Services\NetGSMService;
use App\Models\DeliveryToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CheckFailedWhatsAppMessages extends Command
{
    protected $signature = 'whatsapp:check-failed-messages {--dry-run : Sadece test modunda çalıştır, herhangi bir işlem yapma}';
    protected $description = 'Başarısız WhatsApp mesajlarını kontrol eder ve SMS kuyruğuna ekler';

    public function __construct(
        protected NetGSMService $netGSMService
    ) {
        parent::__construct();
    }

   // CheckFailedWhatsAppMessages.php
public function handle()
{
    $dryRun = $this->option('dry-run');
    
    if ($dryRun) {
        $this->info('DRY RUN MODU: Sadece test amaçlı çalıştırılıyor...');
    }

    $this->info('Başarısız WhatsApp mesajları kontrol ediliyor...');

    $failedLogs = WaMessageLog::query()
        ->where('status', 'failed')
        ->whereNull('sms_attempted_at')
        ->where('created_at', '>=', now()->subMinutes(10))
        ->get();

    if ($dryRun) {
        $this->info("DRY RUN: Toplam {$failedLogs->count()} adet işlenecek mesaj bulundu.");
        return Command::SUCCESS;
    }

    foreach ($failedLogs as $log) {
        try {
         
            $originalMessage = WhatsAppMessage::where('metadata', 'LIKE', '%' . $log->message_id . '%')->first();

            if (!$originalMessage) {
                $this->warn("Orijinal mesaj bulunamadı: {$log->message_id}");
                continue;
            }

            //$phone = $originalMessage->customer->phone;
            $phone = "905385297751";
            $templateName = $originalMessage->type;
            $params = $originalMessage->metadata['template_params'] ?? [];

            // SMS içeriğini oluştur
            // Token olustur PDF için
if ($templateName === 'appointment_checked_in') {
    $token = Str::random(32);
    
    DeliveryToken::create([
        'appointment_id' => $originalMessage->appointment_id,
        'token' => $token,
        'expires_at' => now()->addDays(3) // 3 gün geçerli
    ]);
    
    // PDF görüntüleme linkini oluştur
    $params[5] = route('delivery.pdf', [
        'appointment_id' => $originalMessage->appointment_id,
        'token' => $token
    ]);
}
if ($templateName === 'appointment_completed') {
    $token = Str::random(14);
    
    DeliveryToken::create([
        'appointment_id' => $originalMessage->appointment_id,
        'token' => $token,
        'expires_at' => now()->addDays(3) // 3 gün geçerli
    ]);
    
    // PDF görüntüleme linkini oluştur
    $params[5] = route('pdf', [
        'appointment_id' => $originalMessage->appointment_id,
        'token' => $token
    ]);
    
}
            $smsContent = $this->createSmsFromTemplate($templateName, $params);
             
        
            $this->netGSMService->sendSms($phone, $smsContent, $log->message_id);
            
            
            $this->info("SMS gönderildi - Mesaj ID: {$log->message_id}, Telefon: {$phone}");
            
            // Update sms_attempted_at
            $log->sms_attempted_at = now();
            $updated = $log->save();
            
            if ($updated) {
                $this->info("sms_attempted_at güncellendi: " . $log->sms_attempted_at);
            } else {
                $this->error("sms_attempted_at güncellenemedi!");
                \Log::error('sms_attempted_at güncellenemedi', [
                    'message_id' => $log->message_id,
                    'log' => $log->toArray()
                ]);
            }


        } catch (\Exception $e) {
            $this->error("Hata: " . $e->getMessage());
            \Log::error('SMS gönderilirken hata oluştu', [
                'message_id' => $log->message_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    return Command::SUCCESS;
}



private function createSmsFromTemplate($templateName, $params)
{
    $this->info("SMS oluşturuluyor - Şablon: {$templateName}, Parametreler: " . json_encode($params));
    
    $templates = [
        'appointment_scheduled' => "Sayın {0}, {Patilance'de {1} için {2} randevunuz planlandı.
TARİH: {3}
Sizi ve sevimli dostumuzu bekliyoruz.
Randevunuzdan 15 dakika önce* salonda bulunmanızı rica ederiz. 
Gecikme durumunda randevunuzun saatinde değişiklik veya iptali gerekebilir.
Randevunuzu iptal etmek veya farklı bir işlem için +90(538) 874 09 84 numaralı telefondan bize ulaşabilirsiniz.",
        'appointment_cancelled' => "Sayın {0}, {1} tarihli randevunuz iptal edilmiştir.",
        'appointment_completed' => "Merhaba {0} 
{1} başarıyla Patilance’dan ayrıldı!
Umarız konforlu ve keyifli bir deneyim yaşadı.
PDF’i kontrol ederek hizmet detaylarına ulaşabilirsiniz.
Herhangi bir sorunuz olursa: +90 (538) 874 09 84 numaralı telefondan bizimle iletişime geçebilirsiniz.
Patilance ailesi olarak {2}’i tekrar ağırlamayı dört gözle bekliyoruz!
RANDEVU DETAYLARI: {5}",
        'appointment_checked_in' => "Merhaba {0},  
{1} başarıyla Patilance’a giriş yaptı!
Ekibimiz onun konforu ve güvenliği için hazır.  
Teslim Tutanağına ilettiğimiz bağlantıdan ulaşabilirsiniz.
Herhangi bir sorunuz için  +90(538) 874 09 84 numaralı telefondan bize ulaşabilirsiniz. 
Patilance ailesi olarak {2}’i ağırlamaktan mutluluk duyuyoruz!
TESLIM TUTANAGI: {5}",
'appointment_payment_info' => "Merhaba {0}
Patilance randevunuz başarıyla tamamlanmıştır. 
{{2}}
----
{{3}}
----
Soru ve önerileriniz için +90 (538) 874 09 84 numaralı telefondan bizimle iletişime geçebilirsiniz.
Patilance’i tercih ettiğiniz için teşekkür ederiz.
Sağlıklı ve mutlu günler dileriz."
    ];
    
    $message = $templates[$templateName] ?? 'Önemli bir bildiriminiz var.';
    return preg_replace_callback('/\{(\d+)\}/', function($m) use ($params) {
        return $params[$m[1]] ?? '';
    }, $message);
}
}