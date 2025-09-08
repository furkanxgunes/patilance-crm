<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $query = Appointment::with(['customer', 'pet', 'services'])
            ->when($q, function ($qBuilder) use ($q) {
                $qBuilder->where(function ($sub) use ($q) {
                    $sub->whereHas('customer', function ($c) use ($q) {
                            $c->where('name', 'like', "%{$q}%");
                        })
                        ->orWhereHas('pet', function ($p) use ($q) {
                            $p->where('name', 'like', "%{$q}%");
                        })
                        ->orWhere('id', (int) $q);
                });
            })
            ->when($status, function ($qBuilder) use ($status) {
                $qBuilder->where('status', $status);
            })
            ->orderBy('planned_at', 'desc');
        
        $appointments = $query->paginate(10)->withQueryString();
        $statuses = AppointmentStatus::cases();
        return view('appointments.index', compact('appointments', 'statuses', 'q', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getLastNote(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|integer|exists:customers,id',
            ]);
    
            $last = Appointment::where('customer_id', $request->customer_id)
                ->whereNotNull('notes')
                ->where('notes', '<>', '')
                ->orderByDesc('planned_at')   // yoksa created_at
                ->orderByDesc('id')
                ->first();
    
            if (!$last) {
                return response()->json(['found' => false]); // 200
            }
    
            $plannedAt = null;
            if (!empty($last->planned_at)) {
                $plannedAt = $last->planned_at instanceof \Illuminate\Support\Carbon
                    ? $last->planned_at->format('d.m.Y H:i')
                    : Carbon::parse($last->planned_at)->format('d.m.Y H:i');
            }
    
            return response()->json([
                'found'          => true,
                'appointment_id' => $last->id,
                'notes'          => $last->notes,
                'planned_at'     => $plannedAt,
            ]);
        } catch (\Throwable $e) {
            Log::error('appointments.lastNote error', [
                'customer_id' => $request->input('customer_id'),
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['found' => false]); // 200 ile dön → front kırılmaz
        }
    }
    public function create()
    {
        $customers = \App\Models\Customer::with([
            'segment',
            'pets' => function($query) {
                $query->select('id', 'customer_id', 'name','breed_id')
                      ->withCount(['appointments' => function($q) {
                          $q->whereIn('status', [AppointmentStatus::SCHEDULED, AppointmentStatus::CHECKED_IN]);
                      }]);
            },
            'segment.services',
            'pets.breed' // segment ve indirimli servisleri
        ])->get(['id','name', 'segment_id']);
        // Get pet IDs that have active appointments
        $petsWithAppointments = \App\Models\Pet::whereHas('appointments', function($q) {
            $q->whereIn('status', [AppointmentStatus::SCHEDULED, AppointmentStatus::CHECKED_IN]);
        })->pluck('id')->toArray();
        
    
        
        $services = \App\Models\Service::with('breeds')->get();
        $users = \App\Models\User::all();
        $statuses = AppointmentStatus::cases();
        
            // Her müşteri için segment indirimlerini maple
    $customerSegments = [];
    foreach ($customers as $customer) {
        if ($customer->segment) {
            $discounts = [];
            foreach ($customer->segment->services as $svc) {
                $discounts[$svc->id] = $svc->pivot->discount_percent ?? 0;
            }
            $customerSegments[$customer->id] = $discounts;
        }
    }
    

        return view('appointments.create', compact(
            'customers', 
            'services', 
            'users',
            'statuses',
            'petsWithAppointments',
            'customerSegments',
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'pet_id' => [
                'required',
                'exists:pets,id',
                // Custom validation to check if pet has active appointments
                function ($attribute, $value, $fail) {
                    $hasActiveAppointment = \App\Models\Appointment::where('pet_id', $value)
                        ->whereIn('status', [AppointmentStatus::SCHEDULED, AppointmentStatus::CHECKED_IN])
                        ->exists();
                    
                    if ($hasActiveAppointment) {
                        $fail('Bu evcil hayvanın zaten planlanmış veya devam eden bir randevusu bulunmaktadır.');
                    }
                },
            ],
            'planned_at' => 'required|date_format:Y-m-d\\TH:i',
            'planned_exit' => 'required|date_format:Y-m-d\\TH:i',
            'checkin_at' => 'nullable|date_format:Y-m-d\\TH:i',
            'checkout_at' => 'nullable|date_format:Y-m-d\\TH:i|after:checkin_at',
            'notes' => 'nullable|string',
            'send_notification' => 'nullable|integer',
            'send_notification_checkin' => 'nullable|integer',
            'send_notification_checkout' => 'nullable|integer',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'service_quantities' => 'required|array',
            'service_quantities.*' => 'required|integer|min:1',
            'service_prices' => 'required|array',
            'service_prices.*' => 'required|numeric|min:0',
            'user_id' => 'nullable|array',
            'user_id.*' => 'nullable|exists:users,id',
            'extra_items'   => 'nullable|array',
            'extra_items.*.name'     => 'nullable|string|max:255',
            'extra_items.*.price'    => 'nullable|numeric|min:0',

        ]);
        
        // Start a database transaction
        return \DB::transaction(function () use ($validated) {
            // Create the appointment
            $appointment = Appointment::create([
                'customer_id' => $validated['customer_id'],
                'pet_id' => $validated['pet_id'],
                'planned_at' => $validated['planned_at'],
                'planned_exit' => $validated['planned_exit'],
                'send_notification' => $validated['send_notification'] ?? 0,
                'send_notification_checkin' => $validated['send_notification_checkin'] ?? 0,
                'send_notification_checkout' => $validated['send_notification_checkout'] ?? 0,
                'status' => AppointmentStatus::SCHEDULED,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Get services with their current prices
            $services = \App\Models\Service::whereIn('id', $validated['service_ids'])->get();
            
            // Prepare service data with quantities and prices
            $serviceData = [];
            foreach ($services as $service) {
                $quantity = $validated['service_quantities'][$service->id] ?? 1;
                $discountedPrice = $validated['service_prices'][$service->id] ?? $service->base_price;
                $user_id = $validated['user_id'][$service->id] ?? null;
                $serviceData[$service->id] = [
                    'unit_price' => $service->base_price,
                    'original_price' => $service->base_price,
                    'discounted_price' => $discountedPrice,
                    'quantity' => $quantity,
                    'user_id' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Attach services with their quantities and prices
            $appointment->services()->attach($serviceData);
            
            // Attach extra items
            $extraItems = $validated['extra_items'] ?? [];
            foreach ($extraItems as $item) {
                
                if (empty($item['name']) || empty($item['price'])) {
                    continue;
                }                                            
                $appointment->extraItems()->create([
                    'name' => $item['name'],
                    'price' => $item['price'],
                ]);
            }   
            
            return redirect()->route('appointments.index')->with('success', 'Randevu başarıyla oluşturuldu.');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load([
            'customer',
            'pet',
            'services' => function ($query) {
                $query->withTrashed(); // Soft deleted hizmetleri de dahil et
            },
            'services.breeds' => function ($query) {
                $query->withTrashed(); // Eğer breed'ler de soft delete ise
            }
        ]);
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        $appointment->load(['customer', 'pet', 'services']);
        $customers = \App\Models\Customer::with('pets:id,breed_id,customer_id,name', 'segment.services','pets.breed')->get(['id','name', 'segment_id']);
        $services = \App\Models\Service::with('breeds')->get();
        $users = \App\Models\User::all();
        $statuses = AppointmentStatus::cases();
        // Prepare service data for the view
        $serviceQuantities = [];
        $serviceDiscountedPrices = [];
        $serviceNotes = [];
        // dd($customers[1]->pets[1]->breed->services[6]->pivot->toArray());
        foreach ($appointment->services as $service) {
            $serviceQuantities[$service->id] = $service->pivot->quantity ?? 1;
            $serviceDiscountedPrices[$service->id] = $service->pivot->discounted_price ?? $service->base_price;
            $serviceNotes[$service->id] = $service->pivot->notes ?? '';
        }
        
        return view('appointments.edit', compact(
            'appointment', 
            'customers', 
            'services',
            'statuses',
            'serviceQuantities',
            'serviceDiscountedPrices',
            'serviceNotes',
            'users'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'pet_id' => 'required|exists:pets,id',
            'planned_at' => 'required|date_format:Y-m-d\\TH:i',
            'planned_exit' => 'required|date_format:Y-m-d\\TH:i',
            'checkin_at' => 'nullable|date_format:Y-m-d\\TH:i',
            'checkout_at' => 'nullable|date_format:Y-m-d\\TH:i|after:checkin_at',
            'notes' => 'nullable|string',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'service_quantities' => 'required|array',
            'service_quantities.*' => 'required|integer|min:1',
            'service_discounted_prices' => 'nullable|array',
            'service_discounted_prices.*' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|array',
            'user_id.*' => 'nullable|exists:users,id',
            // Extra items
            'extra_items' => 'nullable|array',
            'extra_items.*.id'       => 'nullable|integer|exists:appointment_extra_items,id',
            'extra_items.*.name'     => 'nullable|string|max:255',
            'extra_items.*.price'    => 'nullable|numeric|min:0',
        ]);
        // Start a database transaction
        return \DB::transaction(function () use ($appointment, $validated) {
            // Update the appointment
            $appointment->update(Arr::except($validated, ['service_ids', 'service_quantities','service_discounted_prices','user_id','extra_items']));

            // Get services with their current prices
            $services = \App\Models\Service::whereIn('id', $validated['service_ids'])->get();
            
            // Prepare service data with quantities and prices
            $serviceData = [];
            foreach ($services as $service) {
                $quantity = $validated['service_quantities'][$service->id] ?? 1;
                $discounted_price = $validated['service_discounted_prices'][$service->id] ?? $service->base_price;
                $user_id = $validated['user_id'][$service->id] ?? null;
                $serviceData[$service->id] = [
                    'unit_price' => $service->base_price,
                    'original_price' => $service->base_price,
                    'discounted_price' => $discounted_price,
                    'quantity' => $quantity,
                    'user_id' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            // Sync services with their quantities and prices
            $appointment->services()->sync($serviceData);



        // Ek ürünleri güncelle (create / update / delete)
        $incomingExtraItems = $validated['extra_items'] ?? [];

        // Mevcut item id’lerini al
        $existingIds = $appointment->extraItems()->pluck('id')->toArray();
        $incomingIds = collect($incomingExtraItems)->pluck('id')->filter()->toArray();

        // Silinecekler: DB’de olup, request’te olmayan id’ler
        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            $appointment->extraItems()->whereIn('id', $toDelete)->delete();
        }

        // Kaydet / güncelle
        foreach ($incomingExtraItems as $item) {
            if (empty($item['name']) || empty($item['price'])) {
                continue;
            }

            if (!empty($item['id'])) {
                // Güncelle
                $appointment->extraItems()->where('id', $item['id'])->update([
                    'name' => $item['name'],
                    'price' => $item['price'],
                ]);
            } else {
                // Yeni ekle
                $appointment->extraItems()->create([
                    'name' => $item['name'],
                    'price' => $item['price'],
                ]);
            }
        }

            $appointment->load(['customer:id,name', 'pet:id,name']);
            $msgName = trim(($appointment->customer->name ?? '') . ' - ' . ($appointment->pet->name ?? ''));
            $successMsg = ($msgName ? ($msgName . ' randevusu güncellendi.') : 'Randevu başarıyla güncellendi.');
            
            return redirect()->route('appointments.index')->with('success', $successMsg);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Appointment $appointment)
    {
        $appointment->delete();

        $message = 'Randevu başarıyla silindi.';

        // AJAX isteği kontrolü
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('appointments.index')->with('success', $message);
    }

    /**
     * Show step-by-step check-in form.
     */
    public function checkinForm(Appointment $appointment)
    {
        $appointment->load(['customer.segment.services', 'pet', 'services']);
        $services = \App\Models\Service::with('breeds')->get();
        $users = \App\Models\User::all();
        $last = \App\Models\Appointment::where('customer_id', $appointment->customer_id)
  ->whereNotNull('notes')->where('notes','<>','')
  ->where('id','<>',$appointment->id) // mevcut randevuyu hariç tutmak istersen
  ->orderByDesc('planned_at')->orderByDesc('id')->first();
        $lastNote = !empty($last->notes) ? $last->notes : '';
        return view('appointments.checkin', compact('appointment', 'services', 'users', 'lastNote'));
    }

    /**
     * Perform the check-in for an appointment.
     */
     public function checkin(Request $request, Appointment $appointment)
    {
        if ($appointment->status !== AppointmentStatus::SCHEDULED) {
            $message = 'Bu randevu için zaten işlem yapılmış.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        $validated = $request->validate([
            'checkin_at' => 'required|date_format:Y-m-d\\TH:i',
            'owner_requests' => 'nullable|string',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'service_quantities' => 'required_with:service_ids|array',
            'service_quantities.*' => 'required_with:service_ids|integer|min:1',
            'send_notification_checkin' => 'nullable|integer',
            'send_notification_checkout' => 'nullable|integer',
            'service_prices' => 'required_with:service_ids|array',
            'service_prices.*' => 'required_with:service_ids|numeric|min:0',
            'user_id' => 'nullable|array',
            'user_id.*' => 'nullable|exists:users,id',
            'extra_items'   => 'nullable|array',
            'extra_items.*.name'     => 'nullable|string|max:255',
            'extra_items.*.price'    => 'nullable|numeric|min:0',
        ]);

        // Start transaction to ensure data consistency
        return \DB::transaction(function () use ($validated, $appointment, $request) {
            // Update appointment status and check-in time
            $appointment->update([
                'status' => AppointmentStatus::CHECKED_IN,
                'checkin_at' => $validated['checkin_at'],
                'owner_requests' => $validated['owner_requests'] ?? $appointment->owner_requests,
                'send_notification_checkin' => $validated['send_notification_checkin'] ?? 0,
            ]);

            if (isset($validated['service_ids'])) {
                // Get services with their current prices
                $services = \App\Models\Service::whereIn('id', $validated['service_ids'])->get();
                
                // Prepare service data with quantities and prices
                $serviceData = [];
                foreach ($services as $service) {
                    $quantity = $validated['service_quantities'][$service->id] ?? 1;
                    $discountedPrice = $validated['service_prices'][$service->id] ?? $service->base_price;
                    $user_id = $validated['user_id'][$service->id] ?? null;
                    $serviceData[$service->id] = [
                        'unit_price' => $service->base_price,
                        'discounted_price' => $discountedPrice,
                        'quantity' => $quantity,
                        'user_id' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                // Sync services with their quantities and prices
                $appointment->services()->sync($serviceData);
                        // Ek ürünleri güncelle (create / update / delete)
                $incomingExtraItems = $validated['extra_items'] ?? [];

                // Mevcut item id’lerini al
                $existingIds = $appointment->extraItems()->pluck('id')->toArray();
                $incomingIds = collect($incomingExtraItems)->pluck('id')->filter()->toArray();

                // Silinecekler: DB’de olup, request’te olmayan id’ler
                $toDelete = array_diff($existingIds, $incomingIds);
                if (!empty($toDelete)) {
                    $appointment->extraItems()->whereIn('id', $toDelete)->delete();
                }

                // Kaydet / güncelle
                foreach ($incomingExtraItems as $item) {
                    if (empty($item['name']) || empty($item['price'])) {
                        continue;
                    }

                    if (!empty($item['id'])) {
                        // Güncelle
                        $appointment->extraItems()->where('id', $item['id'])->update([
                            'name' => $item['name'],
                            'price' => $item['price'],
                        ]);
                    } else {
                        // Yeni ekle
                        $appointment->extraItems()->create([
                            'name' => $item['name'],
                            'price' => $item['price'],
                        ]);
                    }
                }
            }

            $message = 'Randevu için check-in başarıyla tamamlandı.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $message, 
                    'redirect_url' => route('appointments.show', $appointment),
                    'pdf_url' => route('appointments.delivery.pdf', $appointment)
                ]);
            }

            // Başarılı check-in sonrası randevu detay sayfasına yönlendir
            return redirect()->route('appointments.show', $appointment)->with('success', $message);
        });

        // Başarılı check-in sonrası randevu detay sayfasına yönlendir
        return redirect()->route('appointments.show', $appointment)->with('success', $message);
    }

    /**
     * Show step-by-step check-out form.
     */
    public function checkoutForm(Appointment $appointment)
    {
        $appointment->load(['customer', 'pet', 'services']);
        $services = \App\Models\Service::with('breeds')->get();
        $users = \App\Models\User::all();
        $last = \App\Models\Appointment::where('customer_id', $appointment->customer_id)
        ->whereNotNull('notes')->where('notes','<>','')
        ->where('id','<>',$appointment->id) // mevcut randevuyu hariç tutmak istersen
        ->orderByDesc('planned_at')->orderByDesc('id')->first();
              $lastNote = !empty($last->notes) ? $last->notes : '';
        return view('appointments.checkout', compact('appointment', 'services', 'users','lastNote'));
    }

    /**
     * Perform the check-out for an appointment.
     */
    public function checkout(Request $request, Appointment $appointment)
    {
        if ($appointment->status !== AppointmentStatus::CHECKED_IN) {
            $message = 'Sadece check-in yapılmış randevular için check-out yapılabilir.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        $validated = $request->validate([
            'checkout_at' => 'required|date_format:Y-m-d\\TH:i',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'service_quantities' => 'required_with:service_ids|array',
            'service_quantities.*' => 'required_with:service_ids|integer|min:1',
            'service_prices' => 'required_with:service_ids|array',
            'service_prices.*' => 'required_with:service_ids|numeric|min:0',
            'send_notification_checkout' => 'nullable|integer',
            'send_notification_checkin' => 'nullable|integer',
            'pivot' => 'nullable|array',
            'pivot.*.service_id' => 'required_with:pivot|exists:services,id',
            'pivot.*.unit_price' => 'nullable|numeric',
            'pivot.*.discounted_price' => 'nullable|numeric',
            'pivot.*.quantity' => 'nullable|integer|min:1',
            'pivot.*.notes' => 'nullable|string',
            'user_id' => 'nullable|array',
            'user_id.*' => 'nullable|exists:users,id',
            'extra_items'   => 'nullable|array',
            'extra_items.*.name'     => 'nullable|string|max:255',
            'extra_items.*.price'    => 'nullable|numeric|min:0',
        ]);

        // Enforce: checkout_at must be strictly after checkin_at
        $checkinAt = new \DateTime($appointment->checkin_at);
        $checkoutAt = new \DateTime($validated['checkout_at']);
        if ($checkoutAt <= $checkinAt) {
            $message = 'Check-out zamanı, check-in zamanından sonra olmalıdır.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        // Update services with quantities and prices
        if (isset($validated['service_ids'])) {
            $services = \App\Models\Service::whereIn('id', $validated['service_ids'])->get();
            $serviceData = [];
            
            foreach ($services as $service) {
                $quantity = $validated['service_quantities'][$service->id] ?? 1;
                $discountedPrice = $validated['service_prices'][$service->id] ?? $service->base_price;
                $user_id = $validated['user_id'][$service->id] ?? null;
                $serviceData[$service->id] = [
                    'unit_price' => $service->base_price,
                    'discounted_price' => $discountedPrice,
                    'quantity' => $quantity,
                    'user_id' => $user_id,
                    'updated_at' => now(),
                ];
            }
            
            // Sync services with their quantities and prices
            $appointment->services()->sync($serviceData);
                       // Ek ürünleri güncelle (create / update / delete)
                       $incomingExtraItems = $validated['extra_items'] ?? [];

                       // Mevcut item id’lerini al
                       $existingIds = $appointment->extraItems()->pluck('id')->toArray();
                       $incomingIds = collect($incomingExtraItems)->pluck('id')->filter()->toArray();
       
                       // Silinecekler: DB’de olup, request’te olmayan id’ler
                       $toDelete = array_diff($existingIds, $incomingIds);
                       if (!empty($toDelete)) {
                           $appointment->extraItems()->whereIn('id', $toDelete)->delete();
                       }
       
                       // Kaydet / güncelle
                       foreach ($incomingExtraItems as $item) {
                           if (empty($item['name']) || empty($item['price'])) {
                               continue;
                           }
       
                           if (!empty($item['id'])) {
                               // Güncelle
                               $appointment->extraItems()->where('id', $item['id'])->update([
                                   'name' => $item['name'],
                                   'price' => $item['price'],
                               ]);
                           } else {
                               // Yeni ekle
                               $appointment->extraItems()->create([
                                   'name' => $item['name'],
                                   'price' => $item['price'],
                               ]);
                           }
                       }
        }

        $appointment->update([
            'status' => AppointmentStatus::COMPLETED,
            'checkout_at' => $validated['checkout_at'],
            'send_notification_checkout' => $validated['send_notification_checkout'] ?? 0,
        ]);

        $message = 'Randevu başarıyla tamamlandı (check-out).';
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('appointments.show', $appointment),
            ]);
        }

        // Başarılı check-out sonrası Randevu detay sayfasına yönlendir
        return redirect()->route('appointments.show', $appointment)->with('success', $message);
    }

    /**
     * Generate PDF for the appointment details.
     */
    public function pdf(Appointment $appointment)
    {
        $appointment->load([
            'customer',
            'pet',
            'services' => function ($query) {
                $query->withTrashed(); // Soft deleted hizmetleri de dahil et
            },
            'services.breeds' => function ($query) {
                $query->withTrashed(); // Eğer breed'ler de soft delete ise
            }
        ]);

        $pdf = Pdf::loadView('appointments.pdf', [
            'appointment' => $appointment,
        ])->setPaper('a4');

        $filename = 'randevu-' . $appointment->id . '.pdf';
        return $pdf->stream($filename);
    }


    /**
     * Generate 'Hayvan Teslim Tutanağı' PDF after check-in.
     */
    public function deliveryPdf(Appointment $appointment)
    {
        $appointment->load(['customer', 'pet', 'services']);

        $pdf = Pdf::loadView('appointments.delivery_pdf', [
            'appointment' => $appointment,
        ])->setPaper('a4');

        $filename = 'hayvan-teslim-tutanagi-' . $appointment->id . '.pdf';
        return $pdf->stream($filename);
    }
    public function deliveryPdfForWhatsApp(Appointment $appointment)
    {
    $appointment->load(['customer', 'pet', 'services']);

    // PDF oluştur
    $pdf = Pdf::loadView('appointments.delivery_pdf', [
        'appointment' => $appointment,
    ])->setPaper('a4');

    // Geçici dosya adı
    $filename = 'hayvan-teslim-tutanagi-' . $appointment->id . '.pdf';
    $path = storage_path('app/tmp/' . $filename);

    // PDF’i tmp klasörüne kaydet
    $pdf->save($path);

    return $path; // path'i döndürüyoruz ki WhatsApp serviste kullanabilelim
}
public function appointmentPdfForWhatsApp(Appointment $appointment)
{
$appointment->load(['customer', 'pet', 'services', 'services.breeds']);

// PDF oluştur
$pdf = Pdf::loadView('appointments.pdf', [
    'appointment' => $appointment,
])->setPaper('a4');

// Geçici dosya adı
$filename = 'randevu-' . $appointment->id . '.pdf';
$path = storage_path('app/tmp/' . $filename);

// PDF’i tmp klasörüne kaydet
$pdf->save($path);

return $path; // path'i döndürüyoruz ki WhatsApp serviste kullanabilelim
}
}
