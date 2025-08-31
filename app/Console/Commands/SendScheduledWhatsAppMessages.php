<?php
// app/Console/Commands/SendScheduledWhatsAppMessages.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;

class SendScheduledWhatsAppMessages extends Command
{
    protected $signature = 'whatsapp:send-scheduled {--limit=500}';
    protected $description = 'scheduled_at zamanı gelmiş WhatsApp mesajlarını gönderir';

    public function handle(\App\Services\WhatsAppService $service): int
    {
        // --- Teşhis: PHP now() vs DB NOW() göster ---
        $phpNow = now()->toDateTimeString();
        $dbNow  = \DB::selectOne('SELECT NOW() AS now_val')->now_val ?? null;
    
        $this->info('PHP now(): '.$phpNow);
        $this->info('DB  NOW(): '.$dbNow);
    
        $totalScheduled = \App\Models\WhatsAppMessage::where('status','scheduled')->count();
        $this->info('Toplam scheduled (her zaman): '.$totalScheduled);
    
        $dueCountDb = \App\Models\WhatsAppMessage::where('status','scheduled')
            ->whereRaw('scheduled_at <= NOW()')
            ->count();
        $this->info('Zamanı gelmiş (DB NOW): '.$dueCountDb);
    
        if ($dueCountDb === 0) {
            $this->info('Gönderilecek planlı mesaj yok.');
            return self::SUCCESS;
        }
    
        // --- Asıl seçim: DB NOW() ile kıyasla ---
        $messages = \App\Models\WhatsAppMessage::with('customer')
            ->where('status','scheduled')
            ->whereRaw('scheduled_at <= NOW()') // <--- kritik değişiklik
            ->orderBy('scheduled_at')
            ->limit((int)$this->option('limit'))
            ->get();
    
        foreach ($messages as $msg) {
            $to = $msg->customer?->phone;
            if (!$to) {
                $msg->update([
                    'status'   => 'failed',
                    'metadata' => array_merge($msg->metadata ?? [], ['error' => 'missing_customer_phone']),
                ]);
                $this->warn("Mesaj #{$msg->id}: telefon yok -> failed");
                continue;
            }
    
            $template = $msg->metadata['template'] ?? null;
            $params   = $msg->metadata['template_params'] ?? [];
    
            $result = $service->sendMessage($to, $msg->content, $template, $params);
    
            $ok = is_array($result) && ($result['success'] ?? false);
            $msg->update([
                'status'   => $ok ? 'sent' : 'failed',
                'sent_at'  => $ok ? now() : null,
                'metadata' => array_merge($msg->metadata ?? [], ['whatsapp_response' => $result]),
            ]);
    
            $this->info("Mesaj #{$msg->id} -> ".($ok ? 'sent' : 'failed'));
        }
    
        return self::SUCCESS;
    }
}
