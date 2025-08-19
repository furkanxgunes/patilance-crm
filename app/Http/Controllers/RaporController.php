<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service; // Hizmet modelini ekleyin
use App\Models\Customer; // Müşteri modelini ekleyin
use App\Models\Appointment; // Randevu modelini ekleyin
use Carbon\Carbon;

class RaporController extends Controller
{
    /**
     * Rapor ana sayfasını gösterir
     */
    public function index()
    {
        return view('raporlar.index');
    }

    /**
     * Hizmet analiz raporunu gösterir
     */
    public function hizmetAnalizi(Request $request)
    {
        $baslangic = $request->input('baslangic', now()->startOfMonth()->format('Y-m-d'));
        $bitis = $request->input('bitis', now()->endOfMonth()->format('Y-m-d'));
        
        // Hizmet istatistiklerini al
        $hizmetler = Service::with(['appointments' => function($query) use ($baslangic, $bitis) {
            $query->whereBetween('checkin_at', [$baslangic, $bitis])
                  ->where('status', 'completed');
        }])->get();

        // Hizmet başına istatistikleri hesapla
        $hizmetler->each(function($hizmet) use ($baslangic, $bitis) {
            $hizmet->toplam_islem = $hizmet->appointments->count();
            $hizmet->toplam_tutar = 0;
            
            // Her randevu için hizmetlerin toplam fiyatını hesapla
            foreach ($hizmet->appointments as $randevu) {
                $hizmet->toplam_tutar += $randevu->services->sum(function($service) {
                    return $service->pivot->unit_price * ($service->pivot->quantity ?? 1);
                });
            }
        });

        // Toplam işlem sayısına göre sırala
        $hizmetler = $hizmetler->sortByDesc('toplam_islem');
        
        return view('raporlar.hizmet-analizi', compact('hizmetler', 'baslangic', 'bitis'));
    }

    /**
     * Ciro raporunu gösterir
     */
    public function ciroRaporu(Request $request)
    {
        $tarihAraligi = $request->input('tarih_araligi', 'aylik'); // günlük, aylık, yıllık
        $now = Carbon::now();
        
        // Tarih aralığına göre tarih listesi oluştur
        $tarihler = [];
        
        switch ($tarihAraligi) {
            case 'gunluk':
                for ($i = 0; $i < 30; $i++) {
                    $tarihler[] = $now->copy()->subDays($i)->format('Y-m-d');
                }
                $tarihler = array_reverse($tarihler);
                break;
                
            case 'yillik':
                for ($i = 0; $i < 12; $i++) {
                    $tarihler[] = $now->copy()->subMonths($i)->format('Y');
                }
                $tarihler = array_reverse($tarihler);
                $tarihler = array_unique($tarihler);
                break;
                
            default: // aylık
                for ($i = 0; $i < 12; $i++) {
                    $tarihler[] = $now->copy()->subMonths($i)->format('Y-m');
                }
                $tarihler = array_reverse($tarihler);
        }
        
        // Her bir tarih aralığı için ciro hesapla
        $ciroVerileri = [];
        $hizmetCiroDagilimi = [];
        
        // Hizmet bazlı ciro toplamları için dizi oluştur
        $tumHizmetler = Service::pluck('name', 'id');
        $hizmetToplamlari = [];
        
        foreach ($tarihler as $tarih) {
            $sorgu = Appointment::where('status', 'completed')
                ->with('services');
            
            if (strlen($tarih) === 7) { // YYYY-MM formatında ise
                $sorgu->whereYear('checkin_at', substr($tarih, 0, 4))
                     ->whereMonth('checkin_at', substr($tarih, 5, 2));
            } elseif (strlen($tarih) === 4) { // YYYY formatında ise
                $sorgu->whereYear('checkin_at', $tarih);
            } else { // YYYY-MM-DD formatında ise
                $sorgu->whereDate('checkin_at', $tarih);
            }
            
            $appointments = $sorgu->get();
            $toplamCiro = 0;
            
            // Hizmet bazlı ciro hesaplamaları için
            $donemHizmetToplamlari = [];
            
            foreach ($appointments as $appointment) {
                foreach ($appointment->services as $service) {
                    $hizmetTutarı = $service->pivot->discounted_price ?? $service->pivot->unit_price;
                    $adet = $service->pivot->quantity ?? 1;
                    $tutar = $hizmetTutarı * $adet;
                    
                    $toplamCiro += $tutar;
                    
                    // Hizmet bazlı toplamları güncelle
                    if (!isset($donemHizmetToplamlari[$service->id])) {
                        $donemHizmetToplamlari[$service->id] = 0;
                    }
                    $donemHizmetToplamlari[$service->id] += $tutar;
                    
                    // Genel hizmet toplamlarını güncelle
                    if (!isset($hizmetToplamlari[$service->id])) {
                        $hizmetToplamlari[$service->id] = 0;
                    }
                    $hizmetToplamlari[$service->id] += $tutar;
                }
            }
            
            $ciroVerileri[$tarih] = $toplamCiro;
            
            // Dönem hizmet dağılımını kaydet (isteğe bağlı, şu an kullanılmıyor)
            $hizmetCiroDagilimi[$tarih] = $donemHizmetToplamlari;
        }
        
        // Hizmet bazlı ciro dağılımını formatla
        $hizmetCiroDagilimi = collect($tumHizmetler)->map(function($name, $id) use ($hizmetToplamlari) {
            return [
                'id' => $id,
                'name' => $name,
                'toplam_ciro' => $hizmetToplamlari[$id] ?? 0
            ];
        })->filter(function($item) {
            return $item['toplam_ciro'] > 0; // Sadece pozitif ciroya sahip hizmetleri göster
        })->sortByDesc('toplam_ciro')->values();
        
        return view('raporlar.ciro-raporu', compact(
            'ciroVerileri', 
            'tarihAraligi',
            'hizmetCiroDagilimi'
        ));
    }

    /**
     * Müşteri analiz raporunu gösterir
     */
    public function musteriAnalizi(Request $request)
    {
        // Varsayılan tarih aralığı: geçen ay
        $baslangic = $request->input('baslangic', now()->startOfMonth()->format('Y-m-d'));
        $bitis = $request->input('bitis', now()->endOfMonth()->format('Y-m-d'));
        // Tarih formatını doğrula
        try {
            $baslangicTarih = \Carbon\Carbon::parse($baslangic)->startOfDay();
            $bitisTarih = \Carbon\Carbon::parse($bitis)->endOfDay();
        } catch (\Exception $e) {
            // Geçersiz tarih formatı durumunda varsayılan değerlere dön
            $baslangicTarih = now()->startOfMonth()->startOfDay();
            $bitisTarih = now()->endOfMonth()->endOfDay();
            $baslangic = $baslangicTarih->format('Y-m-d');
            $bitis = $bitisTarih->format('Y-m-d');
        }
        
        // En çok hizmet alan müşteriler
        $sadikMusteriler = Customer::with(['appointments' => function($query) use ($baslangicTarih, $bitisTarih) {
            $query->where('status', 'completed')
                  ->whereBetween('checkin_at', [$baslangicTarih, $bitisTarih])
                  ->with('services');
        }])
        ->withCount(['appointments' => function($query) use ($baslangicTarih, $bitisTarih) {
            $query->where('status', 'completed')
                  ->whereBetween('checkin_at', [$baslangicTarih, $bitisTarih]);
        }])
        ->having('appointments_count', '>', 0)
        ->get() 
        ->map(function($customer) use ($baslangicTarih, $bitisTarih) {
            $totalSpent = 0;
            $appointmentCount = 0;
            $sonIslemTarihi = null;
            
            foreach ($customer->appointments as $appointment) {
                $appointmentDate = \Carbon\Carbon::parse($appointment->checkin_at);
                if ($appointmentDate->between($baslangicTarih, $bitisTarih)) {
                    foreach ($appointment->services as $service) {
                        $hizmetTutari = $service->pivot->discounted_price ?? $service->pivot->unit_price;
                        $totalSpent += $hizmetTutari * ($service->pivot->quantity ?? 1);
                    }
                    $appointmentCount++;
                    
                    // Son işlem tarihini güncelle
                    if (!$sonIslemTarihi || $appointmentDate->gt($sonIslemTarihi)) {
                        $sonIslemTarihi = $appointmentDate;
                    }
                }
            }
            
            $customer->toplam_harcama = $totalSpent;
            $customer->toplam_randevu = $appointmentCount;
            $customer->son_islem_tarihi = $sonIslemTarihi ? $sonIslemTarihi->format('d.m.Y H:i') : null;
            return $customer;
        })
        ->filter(function($customer) {
            return $customer->toplam_harcama > 0;
        })
        ->sortByDesc('toplam_randevu')
        ->take(15);
        
        // En çok harcama yapan müşteriler
        $harcamaYapanMusteriler = $sadikMusteriler->sortByDesc('toplam_harcama')
                                                 ->take(15)
                                                 ->values();
                                                 
        // Sıralamayı düzelt
        $sadikMusteriler = $sadikMusteriler->values();
       
        return view('raporlar.musteri-analizi', compact('sadikMusteriler', 'harcamaYapanMusteriler', 'baslangic', 'bitis'));
    }
}
