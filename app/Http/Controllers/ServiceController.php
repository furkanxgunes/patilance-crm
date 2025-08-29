<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Breed;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $services = Service::query()
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('services.index', compact('services', 'q'));
    }

    /**
     * Show the form for creating a new resource.
     */
  public function create()
    {
        $services = Service::all();
        $breeds = Breed::all();
        return view('services.create', compact('services', 'breeds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Formdan gelen veriyi doğrula
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'category' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'breed_prices' => 'nullable|array',
            'breed_prices.*' => 'nullable|numeric|min:0',
        ]);

        //i can breed prices pivot 
        $service = Service::create($request->all());

        $basePrice = $request->base_price;

        $breedPrices = collect($request->input('breed_prices', []))
    ->mapWithKeys(function ($price, $breedId) use ($basePrice) {
        return [$breedId => ['price' => $price !== null && $price !== '' ? $price : $basePrice]];
    });

    $service->breeds()->sync($breedPrices);
  

        // 3. Kullanıcıyı hizmet listesi sayfasına geri yönlendir ve bir başarı mesajı göster
        return redirect()->route('services.index')->with('success', 'Hizmet başarıyla eklendi.');
    }
    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */public function edit(Service $service)
        {
            $services = Service::all();
            return view('services.edit', compact('service', 'services'));
        }

    /**
     * Update the specified resource in storage.
     */
        public function update(Request $request, Service $service)
        {
            // 1. Formdan gelen veriyi doğrula (store ile aynı olabilir)
            $request->validate([
                'name' => 'required|string|max:255|unique:services,name,'.$service->id,
                'category' => 'required|string|max:255',
                'base_price' => 'required|numeric|min:0',
                'duration_minutes' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'breed_prices' => 'nullable|array',
                'breed_prices.*' => 'nullable|numeric|min:0',
            ]);
            // breed_prices içinden sadece dolu olanları al
            $basePrice = $request->base_price;
            $breedPrices = collect($request->input('breed_prices', []))
            ->mapWithKeys(function ($price, $breedId) use ($basePrice) {
                return [$breedId => ['price' => $price !== null && $price !== '' ? $price : $basePrice]];
            });

            // pivot kaydet
            $service->breeds()->sync($breedPrices);

            // 2. Modeli doğrulanmış veriyle güncelle
            $service->update($request->all());

            // 3. Kullanıcıyı hizmet listesine başarı mesajıyla geri yönlendir
            return redirect()->route('services.index')->with('success', 'Hizmet başarıyla güncellendi.');
        }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy(Service $service)
        {
            $service->delete();

            return redirect()->route('services.index')->with('success', 'Hizmet başarıyla silindi.');
        }
}
