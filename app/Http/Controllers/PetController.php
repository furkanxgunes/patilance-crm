<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Customer;
use Illuminate\Http\Request;

class PetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    $q = $request->string('q')->toString();
    $pets = Pet::with('customer')
        ->when($q, function ($builder) use ($q) {
            $builder->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('species', 'like', "%{$q}%")
                    ->orWhere('breed', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%");
                    });
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();
    return view('pets.index', compact('pets', 'q'));
}
    /**
     * Show the form for creating a new resource.
     */ public function create()
    {
        $customers = Customer::orderBy('name')->get(); // Tüm müşterileri al
        return view('pets.create', ['customers' => $customers]);
    }
    // Müşteriye özel pet ekleme 
    public function createForCustomer(Customer $customer)
    {
        return view('pets.create', ['customer' => $customer]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Formdan gelen veriyi doğrula
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'species' => 'required|string|in:Köpek,Kedi', // Sadece bu iki değer kabul edilsin
            'breed' => 'required|string|max:255',
            'age' => 'required|numeric|max:50', // Yaş 50'den büyük olmasın
            'gender' => 'required|string|in:Erkek,Dişi',
            'weight_kg' => 'required|numeric',
        ]);
        
        // 3. Yeni Pet'i oluştur ve kaydet
        $pet = Pet::create($validatedData);

        // 4. Başarıyla eklendiyse ilgili pet'in düzenleme sayfasına yönlendir
        return redirect()->route('pets.edit', $pet)
                         ->with('success', 'Pet başarıyla eklendi, şimdi detayları düzenleyebilirsiniz.');}

    /**
     * Display the specified resource.
     */
    public function show(Pet $pet)
    {
        // Müşteri ve randevu geçmişini (hizmetleriyle) birlikte yükle
        $pet->load('customer');
        $appointments = $pet->appointments()
            ->with(['customer', 'services'])
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('pets.show', compact('pet', 'appointments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pet $pet)
    {
           $customers = Customer::orderBy('name')->get(); // Tüm müşterileri al
    
    return view('pets.edit', compact('pet', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pet $pet)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'species' => 'required|string|in:Köpek,Kedi',
            'breed' => 'nullable|string|max:255',
            'age' => 'nullable|numeric|max:50',
            'gender' => 'nullable|string|in:Erkek,Dişi',
            'weight_kg' => 'nullable|numeric',
            'appearance' => 'nullable|string',
            'special_marks' => 'nullable|string',
            'habits_toilet' => 'nullable|string',
            'vaccines' => 'nullable|string',
            'allergies' => 'nullable|string',
            'veterinarian_info' => 'nullable|string',
            'chip_no' => 'nullable|string|unique:pets,chip_no,' . $pet->id,
            'medications_text' => 'nullable|string',
        ]);
        
        $pet->update($validatedData);

        return redirect()->route('pets.show', $pet)->with('success', 'Evcil hayvan bilgileri başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pet $pet)
    {
        $customerId = $pet->customer_id;
        $pet->delete();

        return redirect()->route('customers.show', $customerId)->with('success', 'Evcil hayvan başarıyla silindi.');
    }

    public function getPetsByCustomerJson(Customer $customer)
    {
        return response()->json($customer->pets);
    }
}
