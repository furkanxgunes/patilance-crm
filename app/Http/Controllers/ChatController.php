<?php

namespace App\Http\Controllers;

use App\Models\WaMessageLog;
use App\Models\WhatsAppMessage;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatController extends Controller
{
    protected function standardizePhoneNumber($phoneNumber)
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($cleanNumber, '90')) {
            $cleanNumber = substr($cleanNumber, 1);
        }
        return $cleanNumber;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
  $query = WhatsAppMessage::with('customer', 'appointment')
    ->when($search, function($q) use ($search) {
        $q->whereHas('customer', function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    })
    ->latest();
        $messages = $query->paginate(20);

        $matchingStandardizedCustomerPhones = [];
        if ($search) {
            $matchingCustomers = Customer::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
                $standardizedSearch = $this->standardizePhoneNumber($search);
                if (!empty($standardizedSearch)) {
                    $query->orWhere('phone', 'like', '%' . $standardizedSearch . '%');
                }
            })->get();

            $matchingStandardizedCustomerPhones = $matchingCustomers->pluck('phone')->map(function($phone) {
                return $this->standardizePhoneNumber($phone);
            })->unique()->toArray();
        }

        $threadsQuery = WaMessageLog::query()
            ->whereNotNull('wa_id')
            ->select([
                'wa_id',
                
                DB::raw('MAX(created_at) AS last_at'), // BURAYI DÜZELTTİK!
        DB::raw('(SELECT message_id FROM wa_message_logs AS last_msg 
                 WHERE last_msg.wa_id = wa_message_logs.wa_id 
                 ORDER BY last_msg.created_at DESC, last_msg.id DESC LIMIT 1) as last_message_id'),
                DB::raw('SUM(CASE WHEN direction="inbound" THEN 1 ELSE 0 END) AS inbound_count'),
                DB::raw('SUM(CASE WHEN direction="outbound" THEN 1 ELSE 0 END) AS outbound_count'),
                // EN SON MESAJIN DURUMUNU VE YÖNÜNÜ ÇEKEN ALT SORGULAR
                DB::raw('(SELECT status FROM wa_message_logs AS last_msg_status
                         WHERE last_msg_status.wa_id = wa_message_logs.wa_id
                         ORDER BY last_msg_status.created_at DESC, last_msg_status.id DESC LIMIT 1) as last_status'),
                DB::raw('(SELECT direction FROM wa_message_logs AS last_msg_direction
                         WHERE last_msg_direction.wa_id = wa_message_logs.wa_id
                         ORDER BY last_msg_direction.created_at DESC, last_msg_direction.id DESC LIMIT 1) as last_status_direction')
                         
            ])
            ->groupBy('wa_id')
            ->orderByDesc('last_at');

        // ... (Arama filtreleme mantığı aynı kalacak)
        if ($search) {
            $standardizedSearch = $this->standardizePhoneNumber($search);
            if (!empty($standardizedSearch) || !empty($matchingStandardizedCustomerPhones)) {
                $threadsQuery->where(function($query) use ($matchingStandardizedCustomerPhones, $standardizedSearch) {
                    if (!empty($matchingStandardizedCustomerPhones)) {
                        $query->whereIn(DB::raw("SUBSTR(REPLACE(REPLACE(wa_id, '+', ''), '90', '0'), 1)"), $matchingStandardizedCustomerPhones);
                    }
                    if (!empty($standardizedSearch)) {
                        $query->orWhere(DB::raw("SUBSTR(REPLACE(REPLACE(wa_id, '+', ''), '90', '0'), 1)"), $standardizedSearch);
                    }
                });
            } else {
                 // Eğer hiç eşleşen müşteri telefonu yoksa ve arama terimi de boşsa,
                 // filtreleme yapmamak veya boş sonuç döndürmek gerekir.
            }
        }
        // ...

        $threads = $threadsQuery->paginate(20);

        $standardizedWaIds = $threads->pluck('wa_id')->map(function($waId) {
            return $this->standardizePhoneNumber($waId);
        })->unique()->toArray();

        $customers = Customer::whereIn('phone', $standardizedWaIds)
        ->get()
        ->keyBy(function($customer) {
            return $this->standardizePhoneNumber($customer->phone);
        });

        $threads->getCollection()->transform(function ($thread) use ($customers) {
            if (is_string($thread->last_at)) {
                $thread->last_at = Carbon::parse($thread->last_at);
            }
            $standardizedThreadWaId = $this->standardizePhoneNumber($thread->wa_id);
            $thread->customer = $customers->get($standardizedThreadWaId);

            return $thread;
        });

        return view('chat.index', compact('threads', 'customers', 'search'));
    }

    protected function parseRawMessageContent($raw, $body, $direction, $defaultLogType)
    {
        $displayContent = $body;
        $logType = $defaultLogType;
        $rawMessageType = null;

        if ($raw && is_array($raw)) {
            $messageValue = null;

            // Raw içinde 'entry' objesi olan loglar (genellikle status veya ilk gelen webhook'lar)
            if (isset($raw['entry'][0]['changes'][0]['value']['messages'][0])) {
                $messageValue = $raw['entry'][0]['changes'][0]['value']['messages'][0];
            }
            // 'direction='inbound' olanlar için 'raw' doğrudan mesaj objesi gibi (sizin SQL dökümünüzdeki gibi)
            else if ($direction === 'inbound') {
                 $messageValue = $raw;
            }

            if ($messageValue) {
                $messageType = $messageValue['type'] ?? null;
                if ($messageType) {
                    $logType = $messageType;
                    $rawMessageType = $messageType;
                }

                if ($messageType === 'reaction' && isset($messageValue['reaction']['emoji'])) {
                    $displayContent = "Müşteri reaksiyonu: " . $messageValue['reaction']['emoji'];
                } elseif ($messageType === 'sticker') {
                    $displayContent = "Müşteri bir çıkartma gönderdi.";
                } elseif ($messageType === 'image') {
                    $displayContent = "Müşteri bir görsel gönderdi.";
                } elseif ($messageType === 'video') {
                    $displayContent = "Müşteri bir video gönderdi.";
                } elseif ($messageType === 'document') {
                    $displayContent = "Müşteri bir belge gönderdi.";
                } elseif ($messageType === 'audio') {
                    $displayContent = "Müşteri bir sesli mesaj gönderdi.";
                }
                // Text mesajın body'si boşsa ve raw'da text.body varsa onu alalım
                if ($messageType === 'text' && empty($body) && isset($messageValue['text']['body'])) {
                     $displayContent = $messageValue['text']['body'];
                }
            }
        }
        return ['content' => $displayContent, 'log_type' => $logType, 'raw_message_type' => $rawMessageType];
    }

    /**
     * Durumların önceliklerini belirler.
     * Index sayfasındaki özet durumları belirlerken kullanılır.
     */
    protected function getStatusPriority($status)
    {
        $statusPriority = [
            'pending' => 0,
            'sent' => 1,
            'delivered' => 2,
            'read' => 3,
            'failed' => 99,
            'Bilinmiyor' => -1, // Varsayılan durum
        ];
        return $statusPriority[$status] ?? -1;
    }

    /**
     * Mevcut bir durumun, yeni bir durumla güncellenip güncellenmeyeceğini kontrol eder.
     */
    protected function shouldUpdateStatus($oldStatus, $newStatus)
    {
        $oldPriority = $this->getStatusPriority($oldStatus);
        $newPriority = $this->getStatusPriority($newStatus);

        // Eğer mevcut durum zaten 'failed' ise ve yeni durum da 'failed' değilse, güncelleme.
        if ($oldStatus === 'failed' && $newStatus !== 'failed') {
            return false;
        }

        // Eğer yeni durum 'failed' ise ve mevcut durum 'failed' değilse, her zaman güncelle.
        if ($newStatus === 'failed' && $oldStatus !== 'failed') {
            return true;
        }

        // Diğer durumlarda, daha yüksek öncelikli olanı al
        return $newPriority > $oldPriority;
    }

    /**
     * Belirli bir WhatsApp ID (telefon numarası) için sohbet detaylarını gösterir.
     * Sadece en son giden mesajı ve tüm gelen mesajları gösterir.
     */
    public function show($wa_id)
    {
        $standardizedWaId = $this->standardizePhoneNumber($wa_id);
        $customer = Customer::where('phone', $standardizedWaId)->first();

        $finalMessages = collect();
        $outboundMessagesToTrack = []; // message_id'ye göre kendi gönderdiğimiz mesajları tutar

        // --- 1. Kendi Gönderdiğimiz WhatsAppMessage Kayıtlarını Önceden Yükle ---
        // Bu kısım, WaMessageLog'larda sadece 'status' yönü olduğu için kritik.
        // Bizim gönderdiğimiz mesajın orijinal içeriğini ve başlangıç durumunu buradan alacağız.
        if ($customer) {
            $ourMessages = $customer->whatsappMessages()->get();
            foreach ($ourMessages as $ourMsg) {
                $externalMessageId = $ourMsg->metadata['whatsapp_response']['message_id'] ?? null;
                if ($externalMessageId) {
                    $outboundMessagesToTrack[$externalMessageId] = (object)[
                        'type' => 'outbound_our_system',
                        'content' => $ourMsg->content,
                        'status' => $ourMsg->status, // Kendi sistemimizdeki başlangıç durumu
                        'timestamp' => $ourMsg->sent_at ?? $ourMsg->created_at,
                        'direction' => 'outbound',
                        'message_id' => $externalMessageId,
                        'log_type' => $ourMsg->type, // appointment_scheduled vb.
                        'reactions' => collect(),
                        'order_id' => $ourMsg->id, // Mesajların orjinal ID'sini tut
                    ];
                }
            }
        }

        // --- 2. Tüm wa_message_logs kayıtlarını çek ---
        $allWaMessageLogs = WaMessageLog::where('wa_id', $wa_id)
            ->whereNotNull('wa_id')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // --- 3. Mesajları Birleştirme Mantığı ---
        // Bu sefer, `finalMessages` koleksiyonunu doğrudan dolduracağız.
        // En son giden mesajı bulmak için ayrı bir mekanizma kullanacağız.

        $latestOutboundMessageInChat = null; // En son giden mesajı tutacak
        $latestOutboundMessageStatus = null; // En son giden mesajın güncel status'ünü tutacak
        $latestOutboundMessageTimestamp = null; // En son giden mesajın güncel timestamp'ini tutacak
        $latestOutboundMessageLogId = null; // En son giden mesajın log id'sini tutacak

        foreach ($allWaMessageLogs as $log) {
            $messageTimestamp = Carbon::parse($log->created_at);
            $currentMessageId = $log->message_id;

            $parsedResult = $this->parseRawMessageContent($log->raw, $log->body, $log->direction, $log->type);
            $displayContent = $parsedResult['content'];
            $logType = $parsedResult['log_type'];
            $rawMessageType = $parsedResult['raw_message_type'];

            // Gelen mesajlar (inbound) veya raw içinde mesaj payload'ı olan status logları
            if ($log->direction === 'inbound' || ($log->direction === 'status' && !empty($displayContent))) {
                if ($rawMessageType === 'reaction') {
                    // Reaksiyonları ayrı bir inbound mesaj gibi gösterelim (basitlik adına)
                    $finalMessages->push((object)[
                        'type' => 'inbound_message',
                        'content' => $displayContent, // "Müşteri reaksiyonu: ❤️" gibi
                        'status' => null,
                        'timestamp' => $messageTimestamp,
                        'direction' => 'inbound',
                        'message_id' => $currentMessageId ?? $log->id,
                        'log_type' => $logType,
                        'reactions' => collect(),
                    ]);
                } else if (!isset($inboundMessagesMap[$currentMessageId ?? $log->id])) { // Mükerrer inboundları engelle
                    $finalMessages->push((object)[
                        'type' => 'inbound_message',
                        'content' => $displayContent,
                        'status' => null,
                        'timestamp' => $messageTimestamp,
                        'direction' => 'inbound',
                        'message_id' => $currentMessageId ?? $log->id,
                        'log_type' => $logType,
                        'reactions' => collect(),
                    ]);
                    $inboundMessagesMap[$currentMessageId ?? $log->id] = true; // Ekledik işaretle
                }
            }
            // Outbound mesaj durumu güncellemeleri
            else if ($log->direction === 'status' && $currentMessageId) {
                // Eğer bu durum güncellemesi, kendi gönderdiğimiz mesajlardan biriyle eşleşiyorsa
                if (isset($outboundMessagesToTrack[$currentMessageId])) {
                    $existingOutbound = $outboundMessagesToTrack[$currentMessageId];

                    // Sadece en son giden mesajın durumunu ve timestamp'ini güncelle
                    // Bu, show sayfasında tek bir giden mesajı göstereceğimiz için önemli
                    if ($latestOutboundMessageInChat === null || $log->created_at->gt($latestOutboundMessageLogId->created_at)) {
                        $latestOutboundMessageLogId = $log; // En son logu takip et
                    }
                    
                    // Eğer bu log, zaten mevcut olan en son outbound logdan daha yeni veya öncelikli ise
                    if ($latestOutboundMessageInChat && $this->shouldUpdateStatus($latestOutboundMessageStatus, $log->status)) {
                        $latestOutboundMessageStatus = $log->status;
                        $latestOutboundMessageTimestamp = isset($log->raw['timestamp']) ? Carbon::createFromTimestamp($log->raw['timestamp']) : $messageTimestamp;
                    } else if ($latestOutboundMessageInChat === null) { // İlk kez bir outbound status logu görüyoruz
                        $latestOutboundMessageStatus = $log->status;
                        $latestOutboundMessageTimestamp = isset($log->raw['timestamp']) ? Carbon::createFromTimestamp($log->raw['timestamp']) : $messageTimestamp;
                    }
                }
            }
        }

        // --- 4. En Son Giden Mesajı finalMessages'a ekle ---
        // Bu, tüm inbound'lar ve varsa en son outbound mesajı birleştirir.
        if ($latestOutboundMessageLogId) {
             // Kontrol: latestOutboundMessageLogId'nin message_id'si ile eşleşen bir ourMessage var mı?
            $finalOutboundContent = "Mesaj içeriği bulunamadı.";
            $finalOutboundLogType = "text";
            $finalOutboundMessageId = $latestOutboundMessageLogId->message_id;

            if (isset($outboundMessagesToTrack[$finalOutboundMessageId])) {
                $ourOriginal = $outboundMessagesToTrack[$finalOutboundMessageId];
                $finalOutboundContent = $ourOriginal->content;
                $finalOutboundLogType = $ourOriginal->log_type;
            }

            $lastOutboundMessage = (object)[
                'type' => 'outbound_last_summary',
                'content' => $finalOutboundContent,
                'status' => $latestOutboundMessageStatus ?: 'Bilinmiyor',
                'timestamp' => $latestOutboundMessageTimestamp ?: Carbon::parse($latestOutboundMessageLogId->created_at),
                'direction' => 'outbound',
                'message_id' => $finalOutboundMessageId,
                'log_type' => $finalOutboundLogType,
                'reactions' => collect(),
            ];
            $finalMessages->push($lastOutboundMessage);
        }

        // --- 5. Tüm Mesajları Son Kez Zaman Damgasına Göre Sırala ---
        $finalMessages = $finalMessages->sortBy('timestamp');

        return view('chat.show', compact('wa_id', 'customer', 'finalMessages'));
    }
}