<?php

namespace App\Http\Controllers;

use App\Models\WaMessageLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Carbon'u import etmeyi unutmayın

class ChatController extends Controller
{
    /**
     * Telefon numarasını karşılaştırma için standardize eder.
     * Örneğin: "+905321234567" -> "05321234567"
     * "05321234567" -> "05321234567"
     * "905321234567" -> "05321234567" (sizin istediğiniz mantıkla)
     */
    protected function standardizePhoneNumber($phoneNumber)
    {
        // Rakamlar dışındaki tüm karakterleri kaldır
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Eğer numara '90' ile başlıyorsa, '90'ı sil
        if (str_starts_with($cleanNumber, '90')) {
            $cleanNumber = substr($cleanNumber, 1); // "90532" -> "0532" yapar
        }
        // Eğer numara '0' ile başlamıyorsa ve '90' da yoksa,
        // ve Customer tablosunda 0 ile tutuluyorsa, burada '0' eklemek gerekebilir.
        // Ancak current logic'iniz 90'ı 0'a çevirip diğerlerini olduğu gibi bırakmak.
        // Bu yüzden mevcut mantığınızı koruyorum.

        return $cleanNumber;
    }

    public function index(Request $request)
    {
        // Arama terimini al
        $search = $request->input('search');

        // Müşteri adına veya numarasına göre filtrelemek için önce müşteri ID'lerini bulalım
        $matchingCustomerIds = [];
        if ($search) {
            $matchingCustomers = Customer::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');

                // Numara araması için standardize edilmiş halini kullan
                $standardizedSearch = $this->standardizePhoneNumber($search);
                if (!empty($standardizedSearch)) {
                    $query->orWhere('phone', 'like', '%' . $standardizedSearch . '%');
                }
            })->pluck('id'); // Sadece ID'leri al

            $matchingCustomerIds = $matchingCustomers->toArray();
        }

        $threadsQuery = WaMessageLog::query()
            ->whereNotNull('wa_id')
            ->select([
                'wa_id',
                DB::raw('MAX(created_at) AS last_at'),
                DB::raw('SUM(CASE WHEN direction="inbound" THEN 1 ELSE 0 END) AS inbound_count'),
                DB::raw('SUM(CASE WHEN direction="outbound" THEN 1 ELSE 0 END) AS outbound_count'),
                DB::raw('(SELECT status FROM wa_message_logs AS last_status
                         WHERE wa_id = wa_message_logs.wa_id
                         ORDER BY created_at DESC LIMIT 1) as last_status'
                )
            ])
            ->groupBy('wa_id')
            ->orderByDesc('last_at');

        // Arama terimi varsa, threads'i filtrele
        if ($search) {
            // standardizePhoneNumber metodunu burada tekrar çağırmamızın nedeni,
            // $threads koleksiyonundaki wa_id'lerin henüz standardize edilmemiş olmasıdır.
            // Bu kısım, performans açısından dikkat edilmesi gereken bir yerdir.
            // Büyük veri setlerinde `whereRaw` ile veritabanı fonksiyonu kullanmak daha iyi olabilir.
            // Ancak şu anki yapıyı koruyarak PHP tarafında filtreleme yapıyoruz.

            // Tüm wa_id'leri çekip PHP tarafında filtrelemek yerine
            // Customer tablosundaki eşleşen telefon numaralarına göre filtreleme yapalım.
            $customerPhoneNumbers = Customer::whereIn('id', $matchingCustomerIds)
                                            ->pluck('phone')
                                            ->map(function($phone) {
                                                return $this->standardizePhoneNumber($phone);
                                            })->toArray();
            
            // Eğer arama sadece telefon numarasına göre yapıldıysa ve eşleşen bir müşteri bulunamadıysa,
            // direkt olarak wa_id'yi arama terimi ile karşılaştıralım (standardize edilmiş haliyle).
            $standardizedSearch = $this->standardizePhoneNumber($search);
            if (!empty($standardizedSearch)) {
                $threadsQuery->where(function ($query) use ($customerPhoneNumbers, $standardizedSearch) {
                    // Müşteri tablosundan eşleşen standardize edilmiş telefon numaralarını içeren wa_id'ler
                    $query->whereIn(DB::raw('SUBSTR(REPLACE(REPLACE(wa_id, "+", ""), "90", "0"), 1)'), $customerPhoneNumbers)
                          // Veya doğrudan wa_id'nin standardize edilmiş hali arama terimiyle eşleşiyorsa
                          ->orWhere(DB::raw('SUBSTR(REPLACE(REPLACE(wa_id, "+", ""), "90", "0"), 1)'), $standardizedSearch);
                });
            } else {
                 $threadsQuery->whereIn(DB::raw('SUBSTR(REPLACE(REPLACE(wa_id, "+", ""), "90", "0"), 1)'), $customerPhoneNumbers);
            }
        }

        $threads = $threadsQuery->paginate(20); // Paginasyonu burada uyguluyoruz

        $standardizedWaIds = $threads->pluck('wa_id')->map(function($waId) {
            return $this->standardizePhoneNumber($waId);
        })->unique()->toArray();

        $customers = Customer::whereIn('phone', $standardizedWaIds)
        ->get()
        ->keyBy(function($customer) {
            return $this->standardizePhoneNumber($customer->phone);
        });

        // Her bir thread objesindeki 'last_at' string'ini Carbon objesine dönüştürün
        // ve müşteri bilgisini thread objesine ekleyin
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

    public function show($wa_id)
    {
        $messages = WaMessageLog::where('wa_id', $wa_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.show', compact('messages', 'wa_id'));
    }
}