<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Segment;
use App\Models\Service;
use App\Models\SegmentServiceDiscount;

class SegmentController extends Controller
{
    // Segment listesi ve hizmet fiyatları
    public function index()
    {
        $segments = Segment::all();
        $services = Service::all();

        return view('segments.index', compact('segments', 'services'));
    }

    // Yeni segment oluştur
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        Segment::create($request->only('name', 'icon'));

        return redirect()->route('segments.index')->with('success', 'Segment başarıyla eklendi.');
    }

    // Inline segment güncelleme (AJAX)
    public function update(Request $request, Segment $segment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $segment->update($request->only('name', 'icon'));

        return response()->json(['success' => true]);
    }

    // Segment silme
    public function destroy(Segment $segment)
    {
        $segment->delete();
        return redirect()->route('segments.index')->with('success', 'Segment silindi.');
    }
    public function updateServices(Request $request)
    {
        $request->validate([
            'segment_id' => 'required|exists:segments,id',
            'service_discounts' => 'required|array',
            'service_discounts.*' => 'nullable|numeric|min:0|max:100',
        ]);
    
        $segmentId = $request->segment_id;
        $segment = Segment::findOrFail($segmentId);
    
        foreach ($request->service_discounts as $serviceId => $discount) {
            SegmentServiceDiscount::updateOrCreate(
                [
                    'segment_id' => $segmentId,
                    'service_id' => $serviceId,
                ],
                [
                    'discount_percent' => $discount ?: 0, // boşsa 0%
                ]
            );
        }
    
        return redirect()->back()->with('success', 'Segment hizmet indirimleri başarıyla güncellendi.');
    }
    
    

    // AJAX: Segmentin hizmet fiyatlarını JSON olarak döndür
    public function getServicesJson(Segment $segment)
    {
        $prices = $segment->serviceDiscounts()->pluck('discount_percent','service_id')->toArray();
        return response()->json($prices);
    }
}
