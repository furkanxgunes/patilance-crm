<?php

namespace App\Http\Controllers;

use App\Models\Service;
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
        return view('services.create'); // Sadece services/create.blade.php dosyasını göster
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Formdan gelen veriyi doğrula
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        // 2. Doğrulanmış veriyle yeni bir Service objesi oluştur ve kaydet
        Service::create($request->all());

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
            return view('services.edit', compact('service'));
        }

    /**
     * Update the specified resource in storage.
     */
        public function update(Request $request, Service $service)
        {
            // 1. Formdan gelen veriyi doğrula (store ile aynı olabilir)
            $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'base_price' => 'required|numeric|min:0',
                'duration_minutes' => 'required|integer|min:0',
                'description' => 'nullable|string',
            ]);

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
