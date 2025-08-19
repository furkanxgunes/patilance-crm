@extends('adminlte::page')

@section('title', 'Randevu Check-out')


@section('content_header')
    <div class="mb-3">
        <h1 class="mb-2">Check-out - Randevu #{{ $appointment->id }}</h1>
        @php
            $breadcrumbs = [
                route('dashboard') => 'Ana Sayfa',
                route('appointments.index') => 'Randevular',
                '' => 'Check-out #' . $appointment->id
            ];
        @endphp
        @include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
    </div>
@stop

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const serviceSearch = document.getElementById('service-search');
    const serviceList = document.getElementById('service-list');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const selectAllBtn = document.getElementById('select-all-services');
    const clearAllBtn = document.getElementById('clear-all-services');

    // Hizmet seçildiğinde veya seçim kaldırıldığında input'ları aktif/pasif yap
    function toggleQuantityInput(checkbox) {
        const serviceItem = checkbox.closest('.service-item');
        const inputs = serviceItem.querySelectorAll('.service-quantity, .service-price, .service-note, .service-user');
        const serviceId = serviceItem.querySelector('.service-checkbox').value;
        const priceInput = serviceItem.querySelector('.service-price');
        
        inputs.forEach(input => {
            if (checkbox.checked) {
                input.removeAttribute('disabled');
                // Eğer fiyat input'u boşsa, servisin temel fiyatını ata
                if (input === priceInput && !input.value) {
                    const service = @json($services->keyBy('id')->toArray());
                    if (service[serviceId]) {
                        input.value = service[serviceId].base_price;
                    }
                }
            } else {
                input.setAttribute('disabled', 'disabled');
            }
        });
    }

    // Sayfa yüklendiğinde seçili hizmetlerin quantity input'larını aktif et
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        if (checkbox.checked) {
            toggleQuantityInput(checkbox);
        }
        
        // Change event'ini dinle
        checkbox.addEventListener('change', function() {
            toggleQuantityInput(this);
        });
    });
    
    // Hizmet arama
    if (serviceSearch && serviceList) {
        serviceSearch.addEventListener('input', function(){
            const searchTerm = this.value.toLowerCase();
            const serviceItems = serviceList.querySelectorAll('.service-item');
            
            serviceItems.forEach(item => {
                const serviceName = item.getAttribute('data-service-name');
                if (serviceName.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    const userInput = document.getElementById('userInput');
        const userResults = document.getElementById('userResults');
        const hiddenUserId = document.getElementById('user_id');
        const users = @json($users);
        const options = [];
    
        users.forEach(user => {
                options.push({
                    label: `${user.name}`,
                    user_id: user.id,
                });             
            });

          // Personel arama render fonksiyonu
function renderUserResults(container, hiddenInput, textInput, items) {
    container.innerHTML = '';
    hiddenInput.value = '';

    items.forEach(item => {
        const a = document.createElement('a');
        a.href = '#';
        a.className = `list-group-item list-group-item-action`;
        a.innerHTML = item.name;
        a.addEventListener('click', function (e) {
            e.preventDefault();
            hiddenInput.value = item.id;
            textInput.value = item.name;
            container.style.display = 'none';
        });
        container.appendChild(a);
    });
    container.style.display = items.length ? 'block' : 'none';
}
// Basit filtreleme
function filterUsers(q) {
    const needle = q.trim().toLowerCase();
    if (!needle) return users.slice(0, 30);
    return users.filter(u => u.name.toLowerCase().includes(needle)).slice(0, 30);
}
// Tüm personel inputları için event bağla
document.querySelectorAll('[id^="userInput"]').forEach(input => {
    const serviceId = input.id.replace('userInput', '');
    const results = document.getElementById('user-results' + serviceId);
    const hidden = document.getElementById('user_id' + serviceId);

    if(hidden.value) {
        // Hidden doluysa input aktif et
        input.removeAttribute('disabled');
        // Display name
        const user = users.find(u => String(u.id) === hidden.value);
        if(user) input.value = user.name;
    }
    
    input.addEventListener('input', function () {
        const list = filterUsers(input.value);
        renderUserResults(results, hidden, input, list);
    });

    input.addEventListener('focus', function () {
        const list = filterUsers(input.value);
        renderUserResults(results, hidden, input, list);
    });

    // Dışarı tıklanınca kapat
    document.addEventListener('click', function (e) {
        if (!results.contains(e.target) && e.target !== input) {
            results.style.display = 'none';
        }
    });
});


    // Tümünü seç/Temizle işlevleri
    function setAllServices(select) {
        serviceCheckboxes.forEach(checkbox => {
            checkbox.checked = select;
            // Trigger change event to update quantity inputs
            const event = new Event('change');
            checkbox.dispatchEvent(event);
            
            // Toggle quantity input
            toggleQuantityInput(checkbox);
        });
    }

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => setAllServices(true));
    }
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', () => setAllServices(false));
    }
});
</script>
@endpush

@push('js')
<script>
// Simple Alpine.js component for step management
document.addEventListener('alpine:init', () => {
    Alpine.data('checkoutSteps', () => ({
        step: 1,
        
        nextStep() {
            if (this.validateStep()) {
                this.step++;
            }
        },
        
        prevStep() {
            this.step--;
        },
        
        validateStep() {
            if (this.step === 1) {
                const inputCheckout = document.getElementById('checkout_at');
                const checkinAtStr = @json(optional($appointment->checkin_at)?->format('Y-m-d\\TH:i'));
                const validationError = document.getElementById('checkout-validation-error');
                
                if (!inputCheckout.value) {
                    validationError.textContent = 'Lütfen check-out tarih ve saatini giriniz.';
                    return false;
                }
                
                if (checkinAtStr) {
                    const co = new Date(inputCheckout.value);
                    const ci = new Date(checkinAtStr);
                    
                    if (co <= ci) {
                        validationError.textContent = 'Check-out tarihi, check-in tarihinden sonra olmalıdır.';
                        return false;
                    }
                }
                
                validationError.textContent = '';
            }
            return true;
        },
        
        // Initialize date picker
        initDatePicker() {
            const inputCheckout = document.getElementById('checkout_at');
            const checkinAtStr = @json(optional($appointment->checkin_at)?->format('Y-m-d\\TH:i'));
            
            if (checkinAtStr && inputCheckout) {
                const checkinDate = new Date(checkinAtStr);
                checkinDate.setHours(checkinDate.getHours() + 1);
                const minDate = checkinDate.toISOString().slice(0, 16);
                inputCheckout.min = minDate;
                
                // Set initial value to now or checkin + 1 hour, whichever is later
                if(!inputCheckout.value){
                    const now = new Date();
                    const initialDate = now > checkinDate ? now : checkinDate;
                    inputCheckout.value = initialDate.toISOString().slice(0, 16);
                }
            }
        }
    }));
});
</script>
@endpush

@push('css')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('js')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="row" x-data="checkoutSteps" x-init="initDatePicker()" x-cloak>
    <div class="col-12 col-lg-10 mx-auto">
        <x-adminlte-callout theme="light" title="Özet">
            <div class="d-flex flex-wrap justify-content-between">
                <div><strong>Müşteri:</strong> {{ $appointment->customer->name }}</div>
                <div><strong>Pet:</strong> {{ $appointment->pet->name }}</div>
                <div><strong>Check-in:</strong> {{ optional($appointment->checkin_at)->format('d.m.Y H:i') ?? '-' }}</div>
            </div>
        </x-adminlte-callout>
        <x-adminlte-callout theme="info" title="Adımlar">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <button type="button" class="btn btn-sm m-1" :class="step===1 ? 'btn-primary' : 'btn-outline-primary'" disabled>1. Zaman</button>
                <button type="button" class="btn btn-sm m-1" :class="step===2 ? 'btn-primary' : 'btn-outline-primary'" disabled>2. Hizmet Fiyatları</button>
                <button type="button" class="btn btn-sm m-1" :class="step===3 ? 'btn-primary' : 'btn-outline-primary'" disabled>3. Onay</button>
            </div>
        </x-adminlte-callout>

        <form action="{{ route('appointments.checkout', $appointment) }}" method="POST">
            @csrf
            @method('PATCH')

            <div x-show="step===1" x-transition.opacity>
                <x-adminlte-card title="Check-out Zamanı" theme="primary" icon="fas fa-clock">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="checkout_at">Check-out Tarih ve Saat</label>
                                <input type="datetime-local" 
                                       class="form-control @error('checkout_at') is-invalid @enderror" 
                                       id="checkout_at" 
                                       name="checkout_at" 
                                       value="{{ optional($appointment->planned_exit)->format('Y-m-d\\TH:i') }}" 
                                       required 
                                       @if($appointment->checkin_at) 
                                           min="{{ $appointment->checkin_at->addHour()->format('Y-m-d\\TH:i') }}"
                                       @endif>
                                <div id="checkout-validation-error" class="text-danger mt-1"></div>
                                @error('checkout_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Check-in</label>
                                <input type="text" class="form-control" value="{{ optional($appointment->checkin_at)->format('d.m.Y H:i') }}" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" @click="nextStep" class="btn btn-primary">Devam</button>
                    </div>
                </x-adminlte-card>
            </div>

            <div x-show="step===2" x-transition.opacity>
                <x-adminlte-card title="Hizmetler ve Fiyatlandırma" theme="primary" icon="fas fa-list">
                    <div class="form-group">
                        <label>Hizmetler</label>
                        <div class="d-flex align-items-center mb-2">
                            <input type="text" id="service-search" class="form-control form-control-sm mr-2" placeholder="Hizmet ara...">
                            <button type="button" id="select-all-services" class="btn btn-sm btn-outline-primary mr-1">Tümünü Seç</button>
                            <button type="button" id="clear-all-services" class="btn btn-sm btn-outline-secondary">Temizle</button>
                        </div>
                        <div id="service-list" class="border rounded p-2" style="max-height: 400px; overflow:auto;">
                            @php($selectedServices = $appointment->services->pluck('id')->toArray())
                            @foreach ($services as $service)
                                @php($quantity = $appointment->services->find($service->id)->pivot->quantity ?? 1)
                                @php($price = $appointment->services->find($service->id)->pivot->discounted_price ?? $service->base_price)
                                @php($savedUserId[$service->id] = $appointment->services->find($service->id)->pivot->user_id ?? '')
                                @php($savedUserName[$service->id] = $users->firstWhere('id', $savedUserId[$service->id])?->name ?? '')
                                <div class="service-item mb-3 p-2 border-bottom" data-service-id="{{ $service->id }}" data-service-name="{{ strtolower($service->name) }}">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="custom-control custom-checkbox flex-grow-1">
                                            <input type="checkbox" 
                                                   class="custom-control-input service-checkbox" 
                                                   id="svc-{{ $service->id }}" 
                                                   name="service_ids[]" 
                                                   value="{{ $service->id }}" 
                                                   {{ in_array($service->id, old('service_ids', $selectedServices)) ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="svc-{{ $service->id }}">{{ $service->name }}</label>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Adet</span>
                                                </div>
                                                <input type="number" 
                                                       name="service_quantities[{{ $service->id }}]" 
                                                       class="form-control form-control-sm service-quantity" 
                                                       min="1" 
                                                       value="{{ old('service_quantities.'.$service->id, $quantity) }}"
                                                       {{ in_array($service->id, old('service_ids', $selectedServices)) ? '' : 'disabled' }}>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">{{ $service->unit === 'day' ? 'Gün' : ($service->unit === 'hour' ? 'Saat' : 'Seans') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Fiyat</span>
                                            </div>
                                            <input type="number" 
                                                   name="service_prices[{{ $service->id }}]" 
                                                   class="form-control form-control-sm service-price" 
                                                   min="0" 
                                                   step="0.01"
                                                   value="{{ old('service_prices.'.$service->id, $price) }}"
                                                   {{ in_array($service->id, old('service_ids', $selectedServices)) ? '' : 'disabled' }}>
                                            <div class="input-group-append">
                                                <span class="input-group-text">₺</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3 mb-2 mb-md-0">
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Personel</span>
                                                    </div>
                                                           <input 
                                                           type="text" 
                                                           id="userInput{{ $service->id }}" 
                                                           class="form-control service-user" 
                                                           placeholder="Personel Seç"
                                                           value="{{ $savedUserName[$service->id] }}"
                                                            autocomplete="off" 
                                                            {{ in_array($service->id, old('service_ids', $selectedServices)) ? '' : 'disabled' }} >

                                                    <input type="hidden" name="user_id[{{ $service->id }}]" id="user_id{{ $service->id }}" value="{{ $savedUserId[$service->id] ?? '' }}">
                                                    <div id="user-results{{ $service->id }}" class="list-group position-absolute w-100" style="z-index:1000; max-height: 240px; overflow:auto; display:none; top:100%;"></div>
                                                </div>
                                            </div>
                                        <div class="col-12 col-md-5 d-none">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend d-none d-md-flex">
                                                    <span class="input-group-text">Not</span>
                                                </div>
                                                <input type="text" 
                                                       name="service_notes[{{ $service->id }}]" 
                                                       class="form-control form-control-sm service-note" 
                                                       placeholder="Not ekle..."
                                                       value="{{ old('service_notes.'.$service->id, $service->pivot->notes ?? '') }}"
                                                       {{ in_array($service->id, old('service_ids', $selectedServices)) ? '' : 'disabled' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('service_ids')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @error('service_quantities.*')
                            <div class="text-danger mt-1">Lütfen tüm seçili hizmetler için geçerli bir miktar girin.</div>
                        @enderror
                        <small class="text-muted d-block mb-2">Hizmetleri seçin ve gerekli bilgileri girin.</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" @click="step=1">Geri</button>
                        <button type="button" class="btn btn-primary" @click="step=3">Devam</button>
                    </div>
                </x-adminlte-card>
            </div>

            <div x-show="step===3" x-transition.opacity>
                <x-adminlte-card title="Onay" theme="success" icon="fas fa-check">
                    <p>Check-out işlemini tamamlamak üzeresiniz. Hizmet fiyatları ve adetler kaydedilecektir.</p>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" @click="prevStep">Geri</button>
                        <button type="submit" class="btn btn-success">Check-out'u Tamamla</button>
                    </div>
                </x-adminlte-card>
            </div>
        </form>
    </div>
</div>
@stop
