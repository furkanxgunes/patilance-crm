<?php

namespace App\Listeners;

use App\Events\AppointmentPaymentStatusChanged;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendPaymentStatusWhatsAppNotification implements ShouldQueue
{
    public bool $afterCommit = true;

    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function handle(AppointmentPaymentStatusChanged $event)
    {
        $appointment = $event->appointment;
        $appointment->loadMissing('customer','services'); // Müşteri bilgisini yükle

        Log::info('SendPaymentStatusWhatsAppNotification tetiklendi', [
            'appointment_id'       => $appointment->id,
            'old_payment_status'   => $event->oldPaymentStatus,
            'new_payment_status'   => $event->newPaymentStatus,
            'send_notification'    => $appointment->send_notification_payment_status,
        ]);

        // Bildirim gönderilmeyecekse veya müşteri/telefon yoksa çık
        if (!$appointment->send_notification_payment_status || !$appointment->customer || empty($appointment->customer->phone)) {
            Log::info('Ödeme durumu bildirimi gönderilmeyecek (send_notification_payment_status=false veya müşteri/telefon yok).', [
                'appointment_id' => $appointment->id
            ]);
            return;
        }

        $customer = $appointment->customer;
        $messageType = 'appointment_payment_info'; // Sabit mesaj tipi
        $template    = WhatsAppTemplate::where('identifier', $messageType)->first();

        if (!$template) {
            Log::error('WhatsApp şablonu bulunamadı: ' . $messageType, [
                'appointment_id' => $appointment->id
            ]);
            return;
        }

        // DUPLICATE GUARD
        $existing = WhatsAppMessage::where('appointment_id', $appointment->id)
            ->where('type', $messageType)
            ->where('payment_status_value', $appointment->payment_status) // Ödeme durumuna göre de duplicate kontrolü yap
            ->whereIn('status', ['scheduled','pending'])
            ->first();

        if ($existing) {
            Log::info('Zaten planlı/pending ödeme durumu WhatsAppMessage var, tekrar oluşturulmadı', [
                'existing_id'        => $existing->id,
                'appointment_id'     => $appointment->id,
                'type'               => $messageType,
                'payment_status_value' => $appointment->payment_status,
                'scheduled_at'       => optional($existing->scheduled_at)->toDateTimeString(),
            ]);
            return;
        }

        // LOCK
        $lockKey = "wa:lock:appt:{$appointment->id}:{$messageType}:{$appointment->payment_status}"; // Ödeme durumunu da lock'a ekle
        $lock = Cache::lock($lockKey, 10); // 10 sn

        if (!$lock->get()) {
            Log::warning('Lock alınamadı (muhtemel eşzamanlı tetiklenme), işlem atlandı', [
                'appointment_id' => $appointment->id,
                'type'           => $messageType,
                'payment_status_value' => $appointment->payment_status,
            ]);
            return;
        }

        try {
            $params  = $this->prepareTemplateParameters($appointment);
            $message = $this->prepareMessage($template, $appointment, $params); // params'ı da geç

            $whatsappMessage = WhatsAppMessage::create([
                'customer_id'        => $customer->id,
                'appointment_id'     => $appointment->id,
                'type'               => $messageType,
                'payment_status_value' => $appointment->payment_status, // Yeni alan
                'content'            => $message,
                'status'             => 'pending', // Hemen göndereceğimiz için pending
                'scheduled_at'       => now(),
                'metadata'           => [
                    'template'        => $template->name,
                    'template_params' => $params,
                    'reason'          => 'payment_status_change',
                ],
            ]);

            $result = $this->whatsappService->sendMessage(
                $customer->phone,
                $message,
                $template->identifier, // Meta'daki template adı
                $params
            );

            $whatsappMessage->update([
                'status'   => ($result['success'] ?? false) ? 'sent' : 'failed',
                'sent_at'  => ($result['success'] ?? false) ? now() : null,
                'metadata' => array_merge($whatsappMessage->metadata ?? [], [
                    'whatsapp_response' => $result
                ])
            ]);

        } finally {
            optional($lock)->release();
        }
    }

    private function prepareTemplateParameters($appointment): array
    {
        $customerName = $appointment->customer->name ?? 'Müşteri';
        
        $totalAmount =  $appointment->totalAmount ?? 10;

        $param2 = '';
        $param3 = '';

        if ($appointment->payment_status) { // Ödeme başarılı
            $param2 = "Ödenen Tutar: " . number_format($totalAmount, 2, ',', '.') . "₺";
            $param3 = "Ödemeniz başarıyla gerçekleşmiştir, teşekkürler.";
        } else { // Ödeme bekleniyor (başarısız veya yapılmamış)
            $param2 = "Ödenecek Tutar: " . number_format($totalAmount, 2, ',', '.') . "₺";
            $param3 = "Ödemeniz henüz gerçekleşmemiştir, en kısa sürede tamamlamanızı rica ederiz.";
        }

        return [
            $customerName, // {{1}}
            $param2,       // {{2}}
            $param3,       // {{3}}
        ];
    }

    // Bu metot WhatsAppService'e şablon ve parametreleri geçirdiği için Listener içinde ekstra string replace yapmaya gerek yok.
    // Ancak loglama veya debug amaçlı content'i görmek isterseniz kullanılabilir.
    private function prepareMessage($template, $appointment, $params): string
    {
        // Template content'i sadece loglama amaçlı burada birleştirilebilir.
        // Asıl mesaj WhatsAppService tarafından template ve parametrelerle oluşturulacak.
        $messageContent = $template->content;
        $messageContent = str_replace('{{1}}', $params[0] ?? '', $messageContent);
        $messageContent = str_replace('{{2}}', $params[1] ?? '', $messageContent);
        $messageContent = str_replace('{{3}}', $params[2] ?? '', $messageContent);
        return $messageContent;
    }
}