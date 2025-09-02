<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SegmentController;
use App\Http\Controllers\BreedController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ChatController;

Route::match(['get','post'], '/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
Route::get('/home', function () {
    return view('dashboard');
});


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/chat/{wa_id}', [ChatController::class, 'show'])->name('chat.show');

Route::get('/whatsapp-messages', [ChatController::class, 'index'])
    ->name('whatsapp-messages.index');

Route::get('/whatsapp-messages/{wa_id}', [ChatController::class, 'show'])
    ->name('whatsapp-messages.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Kullanıcı Yönetimi Rotaları
    Route::resource('users', App\Http\Controllers\UserController::class)->except(['show']);
});


Route::middleware('auth')->group(function () {


    Route::resource('services', App\Http\Controllers\ServiceController::class);
    Route::resource('customers', App\Http\Controllers\CustomerController::class); 
    Route::resource('appointments', App\Http\Controllers\AppointmentController::class);
    Route::resource('campaigns', App\Http\Controllers\CampaignController::class);
    Route::get('/appointments/{appointment}/checkin', [App\Http\Controllers\AppointmentController::class, 'checkinForm'])->name('appointments.checkin.form');
    Route::get('/appointments/{appointment}/checkout', [App\Http\Controllers\AppointmentController::class, 'checkoutForm'])->name('appointments.checkout.form');
    Route::patch('/appointments/{appointment}/checkin', [App\Http\Controllers\AppointmentController::class, 'checkin'])->name('appointments.checkin');
    Route::patch('/appointments/{appointment}/checkout', [App\Http\Controllers\AppointmentController::class, 'checkout'])->name('appointments.checkout');
    Route::get('/appointments/{appointment}/pdf', [App\Http\Controllers\AppointmentController::class, 'pdf'])->name('appointments.pdf');
    Route::get('/appointments/{appointment}/delivery-pdf', [App\Http\Controllers\AppointmentController::class, 'deliveryPdf'])->name('appointments.delivery.pdf');
    // JSON endpoint: seçilen müşterinin pet seçenekleri
    Route::get('/customers/{customer}/pets/options', [App\Http\Controllers\CustomerController::class, 'petsOptions'])->name('customers.pets.options');
    

// PET ROTALARI
    // Tüm petleri listeleme (pets.index sayfası için)
    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');

    // Doğrudan Pet Oluşturma (Müşteri seçimi ile)
    Route::get('/pets/create', [PetController::class, 'create'])->name('pets.create');

    // Müşteriye Bağlı Pet Oluşturma (Müşteri sayfasından erişim için)
    Route::get('/customers/{customer}/pets/create', [PetController::class, 'createForCustomer'])->name('pets.create.for.customer');

    // Pet Kaydetme (Hem doğrudan hem de müşteriye bağlı ekleme için)
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');

    // Pet düzenleme ve güncelleme
    Route::get('/pets/{pet}/edit', [PetController::class, 'edit'])->name('pets.edit');
    Route::get('/pets/{pet}/show', [PetController::class, 'show'])->name('pets.show');
    Route::put('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');

    // Pet silme
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    // Müşteriye ait petleri JSON formatında çekme (eğer AJAX ile kullanılıyorsa)
    Route::get('/customers/{customer}/pets-json', [PetController::class, 'getPetsByCustomerJson'])->name('pets.json');

    // Breed Management
    Route::get('/breeds', [BreedController::class, 'index'])->name('breeds.index');
    Route::post('/breeds', [BreedController::class, 'store'])->name('breeds.store');
    Route::put('/breeds/{breed}', [BreedController::class, 'update'])->name('breeds.update');
    Route::delete('/breeds/{breed}', [BreedController::class, 'destroy'])->name('breeds.destroy');

// Segmentler
Route::prefix('segments')->name('segments.')->group(function () {
    Route::get('/', [SegmentController::class, 'index'])->name('index');
    Route::post('/', [SegmentController::class, 'store'])->name('store');
    Route::put('/{segment}', [SegmentController::class, 'update'])->name('update');
    Route::delete('/{segment}', [SegmentController::class, 'destroy'])->name('destroy');

    Route::post('/services/update', [SegmentController::class, 'updateServices'])->name('services.update');

    // AJAX endpoint
    Route::get('/{segment}/services/json', [SegmentController::class, 'getServicesJson']);
});


    Route::post('/appointments/last-note', [AppointmentController::class, 'getLastNote'])
        ->name('appointments.lastNote');


});

Route::get('/pdf/{filename}', function ($filename) {
    $path = storage_path('app/tmp/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});
require __DIR__.'/auth.php';

// Raporlama Rotaları
Route::middleware('auth')->prefix('raporlar')->name('raporlar.')->group(function () {
    Route::get('/', [\App\Http\Controllers\RaporController::class, 'index'])->name('index');
    Route::get('/hizmet-analizi', [\App\Http\Controllers\RaporController::class, 'hizmetAnalizi'])->name('hizmet-analizi');
    Route::get('/ciro-raporu', [\App\Http\Controllers\RaporController::class, 'ciroRaporu'])->name('ciro-raporu');
    Route::get('/musteri-analizi', [\App\Http\Controllers\RaporController::class, 'musteriAnalizi'])->name('musteri-analizi');
});
