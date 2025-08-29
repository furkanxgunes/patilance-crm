<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Segment;

class CustomerController extends Controller
{
    

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $customers = Customer::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('customers.index', compact('customers', 'q'));
    }

    /**
     * Show the form for creating a new resource.
     */public function create()
        {
            $segments = Segment::all();
            return view('customers.create', compact('segments'));
        }

public function store(Request $request)
{
    // 1. Veriyi doğrula
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:customers', // email benzersiz olmalı
        'phone' => 'nullable|string|max:255|unique:customers',        
        'address' => 'nullable|string',
        'notes' => 'nullable|string',
        'segment_id' => 'nullable|exists:segments,id',
    ]);

    // 2. Müşteriyi yarat
    Customer::create($request->all());

    // 3. Listeye başarı mesajıyla yönlendir
    return redirect()->route('customers.index')->with('success', 'Müşteri başarıyla eklendi.');
}

    /**
     * Display the specified resource.
     */
        public function show(Request $request, Customer $customer)
        {
            // Müşterinin pet'lerini ve randevu geçmişini (hizmetleriyle) yükle
            $customer->load('pets');
            $appointments = $customer->appointments()
                ->with(['pet', 'services'])
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return view('customers.show', compact('customer', 'appointments'));
        }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
            $segments = Segment::all();
            return view('customers.edit', compact('customer', 'segments'));

    }

    /**
     * Update the specified resource in storage.
     */
 public function update(Request $request, Customer $customer)
{
    // 1. Veriyi doğrula (email'in benzersizlik kontrolü güncellenen kişi hariç tutulmalı)
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:customers,email,' . $customer->id,
        'phone' => 'nullable|string|max:255|unique:customers,phone,' . $customer->id,
        'address' => 'nullable|string',
        'notes' => 'nullable|string',
        'segment_id' => 'nullable|exists:segments,id',
    ]);
    // 2. Müşteriyi güncelle
    $customer->update($request->all());

    // 3. Detay sayfasına yönlendir (mesaj burada görünsün)
    return redirect()->route('customers.show', $customer)->with('success', 'Müşteri başarıyla güncellendi.');
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Müşteri başarıyla silindi.');
    }

    // JSON: Seçilen müşterinin pet seçenekleri (id, name)
    public function petsOptions(Customer $customer)
    {
        $pets = $customer->pets()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($pets);
    }
}
