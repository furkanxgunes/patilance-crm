<?php

namespace App\Listeners;

use App\Enums\AppointmentStatus;
use App\Events\AppointmentStatusChanged;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;

class SendAppointmentWhatsAppNotification implements ShouldQueue
{
    public bool $afterCommit = true;

    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function handle(AppointmentStatusChanged $event)
    {
        $result = ['success' => false];

        $appointment = $event->appointment;
        $appointment->loadMissing('customer','pet','services');

        Log::info('SendAppointmentWhatsAppNotification tetiklendi', [
            'appointment_id' => $appointment->id,
            'old_status'     => $event->oldStatus,
            'new_status'     => $event->newStatus
        ]);

        // 0) scheduled ise ve kullanıcı bildirim istemediyse çık
        if ($event->newStatus->value === 'scheduled' && !$appointment->send_notification) {
            Log::info('Bildirim istenmedi (send_notification=0), çıkılıyor', [
                'appointment_id' => $appointment->id
            ]);
            return;
        }

        // 1) Müşteri/telefon kontrolü
        $customer = $appointment->customer;
        if (!$customer || empty($customer->phone)) {
            Log::warning('Müşteri veya telefon bulunamadı', ['appointment_id' => $appointment->id]);
            return;
        }

        // 2) Mesaj tipi & template
        $messageType = $this->determineMessageType($event->newStatus);
        $template    = $this->getTemplateForStatus($event->newStatus);
        if (!$template) {
            Log::error('WhatsApp şablonu bulunamadı', [
                'status' => $event->newStatus,
                'appointment_id' => $appointment->id
            ]);
            return;
        }

        // 3) **DUPLICATE GUARD — en başa aldık**
        // Aynı appointment + type için o an zaten queued/pending/scheduled/sent bir kayıt varsa tekrar etme.
        $existing = WhatsAppMessage::where('appointment_id', $appointment->id)
            ->where('type', $messageType)
            ->whereIn('status', ['scheduled','pending'])
            ->first();

        if ($existing) {
            Log::info('Zaten planlı/pending WhatsAppMessage var, tekrar oluşturulmadı', [
                'existing_id'  => $existing->id,
                'appointment_id' => $appointment->id,
                'type'         => $messageType,
                'scheduled_at' => optional($existing->scheduled_at)->toDateTimeString(),
            ]);
            return;
        }

        // 4) **LOCK — aynı anda iki tetiklenmeye karşı**
        $lockKey = "wa:lock:appt:{$appointment->id}:{$messageType}";
        $lock = Cache::lock($lockKey, 10); // 10 sn

        if (!$lock->get()) {
            Log::warning('Lock alınamadı (muhtemel eşzamanlı tetiklenme), işlem atlandı', [
                'appointment_id' => $appointment->id,
                'type'           => $messageType
            ]);
            return;
        }

        try {
            // Parametre ve mesaj hazırla
            $params  = $this->prepareTemplateParameters($appointment);
            $message = $this->prepareMessage($template, $appointment);

            // checked_in / completed => PDF gönderimi (aynen)
            if ($event->newStatus->value === 'checked_in' && $appointment->send_notification_checkin) {
                $result = $this->sendDeliveryPdf($appointment, $customer->phone);
            } elseif ($event->newStatus->value === 'completed' && $appointment->send_notification_checkout) {
                $result = $this->sendCompletedPdf($appointment, $customer->phone);
            }

            // **WhatsAppMessage kaydını OLUŞTURMADAN önce** 24s kuralını uygula
            $sendNow    = true;
            $scheduledAt = now();

            if ($event->newStatus === AppointmentStatus::SCHEDULED) {
                // planned_at'e 24 saatten fazla varsa 24 saat öncesine planla
                $plannedAt = $appointment->planned_at?->copy();
                if ($plannedAt) {
                    $twentyFourBefore = $plannedAt->copy()->subDay();
                    if (now()->lt($twentyFourBefore)) {
                        $sendNow     = false;
                        $scheduledAt = $twentyFourBefore;
                        $scheduleReason = 'send_24h_before';
                    } else {
                        $scheduleReason = 'send_immediately_lt_24h';
                    }
                } else {
                    $scheduleReason = 'no_planned_at_send_immediately';
                }
            } else {
                $scheduleReason = 'status_immediate';
            }

            // **KAYIT burada oluşturuluyor** (artık duplicate guard üstte)
            $whatsappMessage = WhatsAppMessage::create([
                'customer_id'    => $customer->id,
                'appointment_id' => $appointment->id,
                'type'           => $messageType,
                'content'        => $message,
                'status'         => $sendNow ? 'pending' : 'scheduled',
                'scheduled_at'   => $sendNow ? now() : $scheduledAt,
                'metadata'       => [
                    'template'         => $template->name,
                    'template_params'  => $params,
                    'reason'           => $scheduleReason,
                ],
            ]);

            // < 24 saat ise hemen gönder (yalnızca SCHEDULED durumunda)
            if ($event->newStatus === AppointmentStatus::SCHEDULED) {
                if ($sendNow) {
                    $result = $this->whatsappService->sendMessage(
                        $customer->phone,
                        $message,
                        $messageType,
                        $params
                    );
                    $whatsappMessage->update([
                        'status'   => ($result['success'] ?? false) ? 'sent' : 'failed',
                        'sent_at'  => ($result['success'] ?? false) ? now() : null,
                        'metadata' => array_merge($whatsappMessage->metadata ?? [], [
                            'whatsapp_response' => $result
                        ])
                    ]);
                } else {
                    Log::info('Mesaj 24 saat öncesine planlandı', [
                        'appointment_id' => $appointment->id,
                        'scheduled_at'   => $scheduledAt->toDateTimeString()
                    ]);
                }
            }

            // checked_in / completed için (yukarıda PDF gönderdiğimiz) sonucu DB’ye işle
            if (in_array($event->newStatus->value, ['checked_in','completed'], true)) {
                if (!empty($result)) {
                    $whatsappMessage->update([
                        'status'   => ($result['success'] ?? false) ? 'sent' : 'failed',
                        'sent_at'  => ($result['success'] ?? false) ? now() : null,
                        'metadata' => array_merge($whatsappMessage->metadata ?? [], [
                            'whatsapp_response' => $result
                        ])
                    ]);
                }
            }

        } finally {
            optional($lock)->release();
        }
    }

    private function determineMessageType($status): string
    {
        return match($status->value) {
            'scheduled' => 'appointment_scheduled',
            'checked_in' => 'appointment_checked_in',
            'completed' => 'appointment_completed',
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
            'pet_name'      => $params[1] ?? 'Minik Dostunuz',
        ];

        $message = $template->content;
        $message = str_replace(
            ['{{customer_name}}','{{pet_name}}'],
            [$variables['customer_name'],$variables['pet_name']],
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
        if ($appointment->status === AppointmentStatus::COMPLETED) {
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

    private function sendDeliveryPdf($appointment, $phone): array
    {
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

        $params = [
            $appointment->customer->name ?? 'Müşteri',
            $appointment->pet->name ?? 'Minik Dostunuz',
            $appointment->pet->name ?? 'Minik Dostunuz',
        ];

        $result = $this->whatsappService->sendMessageWithDocument(
            $phone,
            'Teslim tutanağı ekte yer almaktadır.',
            $fullPath,
            'appointment_checked_in',
            $params
        );

        Log::info('PDF gönderim sonucu (checked_in)', $result);
        return $result;
    }

    private function sendCompletedPdf($appointment, $phone): array
    {
        $tmpPath = storage_path('app/tmp');
        if (!file_exists($tmpPath)) {
            mkdir($tmpPath, 0755, true);
        }

        $filename = 'randevu-' . $appointment->id . '.pdf';
        $fullPath = $tmpPath . '/' . $filename;

        $pdf = Pdf::loadView('appointments.pdf', [
            'appointment' => $appointment
        ])->setPaper('a4');
        $pdf->save($fullPath);

        $params = [
            $appointment->customer->name ?? 'Müşteri',
            $appointment->pet->name ?? 'Minik Dostunuz',
            $appointment->pet->name ?? 'Minik Dostunuz',
        ];

        $result = $this->whatsappService->sendMessageWithDocument(
            $phone,
            'Randevu detayları ekte yer almaktadır.',
            $fullPath,
            'appointment_completed',
            $params
        );

        Log::info('PDF gönderim sonucu (completed)', $result);
        return $result;
    }
}
