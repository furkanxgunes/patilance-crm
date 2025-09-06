<?php

namespace App\Http\Controllers;

use App\Models\WaMessageLog;
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

        $matchingStandardizedCustomerPhones = [];
        if ($search) {
            $matchingCustomers = Customer::where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('surname', 'like', '%' . $search . '%');

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

    public function show($wa_id)
    {
        $messages = WaMessageLog::where('wa_id', $wa_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.show', compact('messages', 'wa_id'));
    }
}