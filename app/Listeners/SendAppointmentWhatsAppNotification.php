<?php

// app/Listeners/SendAppointmentWhatsAppNotification.php

namespace App\Listeners;

use App\Enums\AppointmentStatus;
use App\Events\AppointmentStatusChanged;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class SendAppointmentWhatsAppNotification implements ShouldQueue
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function handle(AppointmentStatusChanged $event)
    {
        $appointment = $event->appointment;

        Log::info('SendAppointmentWhatsAppNotification tetiklendi', [
            'appointment_id' => $appointment->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus
        ]);

        // Eğer yeni bir randevu oluşturuluyorsa ve bildirim istenmiyorsa çık
        if ($event->newStatus->value === 'scheduled') {
            $sendNotification = $appointment->send_notification;
            if (!$sendNotification) {
                Log::info('Bildirim istenmediği için gönderilmedi', [
                    'appointment_id' => $appointment->id
                ]);
                return;
            }
        }

        // Müşteri ilişkisini yükle
        $appointment->loadMissing('customer', 'pet', 'services');
        $customer = $appointment->customer;

        if (!$customer || empty($customer->phone)) {
            Log::warning('Müşteri veya telefon bulunamadı', [
                'appointment_id' => $appointment->id
            ]);
            return;
        }

        // Mesaj tipi ve şablon
        $messageType = $this->determineMessageType($event->newStatus);
        $template = $this->getTemplateForStatus($event->newStatus);

        if (!$template) {
            Log::error('WhatsApp şablonu bulunamadı', [
                'status' => $event->newStatus,
                'appointment_id' => $appointment->id
            ]);
            return;
        }

        // Template mesajı hazırla
        $params = $this->prepareTemplateParameters($appointment);
        $message = $this->prepareMessage($template, $appointment);

        // Eğer checked_in ise PDF gönder
        if ($event->newStatus->value === 'checked_in') {
            $tmpPath = storage_path('app/tmp');
            if (!file_exists($tmpPath)) {
                mkdir($tmpPath, 0755, true);
            }

            $filename = 'hayvan-teslim-tutanagi-' . $appointment->id . '.pdf';
            $fullPath = $tmpPath . '/' . $filename;

            $pdf = Pdf::loadView('appointments.delivery_pdf', [
                'appointment' => $appointment
            ])->setPaper('a4');
            $pdf->save($fullPath);

              // WhatsApp template parametreleri
            $params = [
                $appointment->customer->name ?? 'Müşteri',   // body param
                $appointment->pet->name ?? 'Minik Dostunuz',  // body param
                $appointment->pet->name ?? 'Minik Dostunuz'  // body param
                // Document zaten header olarak gönderildiği için link parametresi burada yok
            ];

            // WhatsAppService'e gönder (sadece document)
            $result = $this->whatsappService->sendMessageWithDocument(
                $customer->phone,
                'Teslim tutanağı ekte yer almaktadır.', // caption
                $fullPath,
                'appointment_checked_in',
                $params
            );

            // PDF’i sil
            if (file_exists($fullPath)) {
                // unlink($fullPath);
            }

            Log::info('PDF gönderim sonucu', $result);
        }

        // Mesajı veritabanına kaydet
        $whatsappMessage = WhatsAppMessage::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'type' => $messageType,
            'content' => $message,
            'status' => 'pending',
            'scheduled_at' => now(),
            'metadata' => [
                'template' => $template->name,
                'status' => $event->newStatus
            ]
        ]);

        if ($event->newStatus === AppointmentStatus::SCHEDULED) {
              // Template mesajı gönder
            $result = $this->whatsappService->sendMessage(
                $customer->phone,
                $message,
                $messageType,
                $params
            );
        } 
      

        // Durumu güncelle
        $whatsappMessage->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'sent_at' => $result['success'] ? now() : null,
            'metadata' => array_merge($whatsappMessage->metadata ?? [], [
                'whatsapp_response' => $result,
                'template_params' => $params
            ])
        ]);

        Log::info('WhatsApp mesaj gönderimi tamamlandı', [
            'appointment_id' => $appointment->id,
            'result' => $result
        ]);
    }

    private function determineMessageType($status): string
    {
        return match($status->value) {
            'scheduled' => 'appointment_scheduled',
            'checked_in' => 'appointment_checked_in',
            'completed' => 'checkout_confirmation',
            'cancelled' => 'appointment_cancelled',
            default => 'appointment_updated',
        };
    }

    private function getTemplateForStatus($status)
    {
        $templateName = $this->determineMessageType($status);
        return WhatsAppTemplate::where('identifier', $templateName)->first();
    }

    private function prepareMessage($template, $appointment): string
    {
        $params = $this->prepareTemplateParameters($appointment);

        $variables = [
            'customer_name' => $params[0] ?? 'Müşteri',
            'pet_name' => $params[1] ?? 'Minik Dostunuz',
        ];

        $message = $template->content;
        $message = str_replace(
            ['{{customer_name}}', '{{pet_name}}'],
            [$variables['customer_name'], $variables['pet_name']],
            $message
        );

        return $message;
    }

    private function prepareTemplateParameters($appointment): array
    {
        if ($appointment->status === AppointmentStatus::CHECKED_IN) {
            return [
                $appointment->customer->name ?? 'Müşteri',
                $appointment->pet->name ?? 'Minik Dostunuz',
                $appointment->pet->name ?? 'Minik Dostunuz',
            ];
        }

        return [
            $appointment->customer->name ?? 'Müşteri',
            $appointment->pet->name ?? 'Minik Dostunuz',
            $appointment->service_names ?? 'Patilance',
            $appointment->planned_at ? $appointment->planned_at->format('d.m.Y H:i') : 'Belirtilmemiş'
        ];
    }
}
