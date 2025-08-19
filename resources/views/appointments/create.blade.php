@extends('adminlte::page')

@section('title', 'Yeni Randevu Oluştur')

{{-- No WYSIWYG needed for notes on create --}}

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Yeni Randevu Oluştur</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Randevular</a></li>
                <li class="breadcrumb-item active" aria-current="page">Yeni</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-10 mx-auto">
            <x-adminlte-card title="Randevu Formu" theme="info" icon="fas fa-calendar-plus" collapsible>

                <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf

                    {{-- Müşteri & Evcil Hayvan (Birleşik Aramalı Seçim) --}}
                    <div class="form-group row">
                        <label for="cp-input" class="col-sm-3 col-form-label">Müşteri & Evcil Hayvan</label>
                        <div class="col-sm-9 position-relative">
                            <input type="text" id="cp-input" class="form-control" placeholder="Müşteri veya evcil hayvan ara..." autocomplete="off">
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">
                            <input type="hidden" name="pet_id" id="pet_id" value="{{ old('pet_id') }}">
                            <div id="cp-results" class="list-group position-absolute w-100" style="z-index:1000; max-height: 240px; overflow:auto; display:none;"></div>
                            @error('customer_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            @error('pet_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Hizmetler: checkbox list with search and bulk actions --}}
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Hizmetler</label>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center mb-2">
                                <input type="text" id="service-search" class="form-control form-control-sm mr-2" placeholder="Hizmet ara...">
                                <button type="button" id="select-all-services" class="btn btn-sm btn-outline-primary mr-1">Tümünü Seç</button>
                                <button type="button" id="clear-all-services" class="btn btn-sm btn-outline-secondary">Temizle</button>
                            </div>
                            <div id="service-list" class="border rounded p-2" style="max-height: 400px; overflow:auto;">
                                @foreach ($services as $service)
                                    <div class="service-item mb-3 p-2 border-bottom" data-service-id="{{ $service->id }}" data-service-name="{{ strtolower($service->name) }}">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="custom-control custom-checkbox flex-grow-1">
                                                <input type="checkbox" 
                                                       class="custom-control-input service-checkbox" 
                                                       id="svc-{{ $service->id }}" 
                                                       name="service_ids[]" 
                                                       value="{{ $service->id }}" 
                                                       {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}>
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
                                                           value="{{ old('service_quantities.'.$service->id, 1) }}"
                                                           {{ in_array($service->id, old('service_ids', [])) ? '' : 'disabled' }}>
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
                                                           value="{{ old('service_prices.'.$service->id, $service->base_price) }}"
                                                           {{ in_array($service->id, old('service_ids', [])) ? '' : 'disabled' }}>
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
                                                           {{ in_array($service->id, old('service_ids', [])) ? '' : 'disabled' }}
                                                            autocomplete="off" >
                                                    <input type="hidden" name="user_id[{{ $service->id }}]" id="user_id{{ $service->id }}" value="{{ old('user_id.$service->id', $service->pivot->user_id ?? '') }}">
                                                    <div id="user-results{{ $service->id }}" class="list-group position-absolute w-100" style="z-index:1000; max-height: 240px; overflow:auto; display:none; top:100%;"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-5 d-none" hidden>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend d-none d-md-flex">
                                                        <span class="input-group-text">Not</span>
                                                    </div>
                                                    <input type="text" 
                                                           name="service_notes[{{ $service->id }}]" 
                                                           class="form-control form-control-sm service-note" 
                                                           placeholder="Not ekle..."
                                                           value="{{ old('service_notes.'.$service->id, '') }}"
                                                           {{ in_array($service->id, old('service_ids', [])) ? '' : 'disabled' }}>
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
                            <small class="text-muted d-block mb-2">Hizmetleri seçin ve gerekli bilgileri girin. Fiyatlar otomatik olarak doldurulacaktır.</small>
                        </div>
                    </div>

                    {{-- Planlanan Tarih/Saat --}}
                    <div class="form-group row">
                        <label for="planned_at" class="col-sm-3 col-form-label">Planlanan Tarih/Saat</label>
                        <div class="col-sm-4">
                            <input type="datetime-local" name="planned_at" id="planned_at" class="form-control" required>
                            <small class="text-muted d-block mb-2">Tahmini Giriş Zamanı</small>

                            @error('planned_at')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <input type="datetime-local" name="planned_exit" id="planned_exit" class="form-control" required>
                            <small class="text-muted d-block mb-2">Tahmini Çıkış Zamanı</small>

                            @error('planned_exit')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Notlar --}}
                    <div class="form-group row">
                        <label for="notes" class="col-sm-3 col-form-label">Notlar</label>
                        <div class="col-sm-9">
                            <textarea name="notes" id="notes" rows="4" class="form-control" placeholder="Randevu ile ilgili notlar...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- WhatsApp Bildirim --}}
                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="send_notification" name="send_notification" value="1" checked>
                                <label class="custom-control-label" for="send_notification">Müşteriye WhatsApp ile bildirim gönder</label>
                                <small class="form-text text-muted">Randevu oluşturulduğunda müşteriye otomatik WhatsApp bildirimi gönderilir.</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <x-adminlte-button type="submit" label="Kaydet" theme="success" icon="fas fa-save"/>
                    </div>
                </form>

            </x-adminlte-card>
        </div>
    </div>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Birleşik müşteri-evcil hayvan arama
            const cpInput = document.getElementById('cp-input');
            const cpResults = document.getElementById('cp-results');
            const userInput = document.getElementById('userInput');
            const userResults = document.getElementById('userResults');
            const hiddenCustomerId = document.getElementById('customer_id');
            const hiddenPetId = document.getElementById('pet_id');
            const hiddenUserId = document.getElementById('user_id');
            const serviceSearch = document.getElementById('service-search');
            const serviceList = document.getElementById('service-list');
            const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
            const selectAllBtn = document.getElementById('select-all-services');
            const clearAllBtn = document.getElementById('clear-all-services');

            // Hizmet seçildiğinde veya seçim kaldırıldığında input'ları aktif/pasif yap
            function toggleQuantityInput(checkbox) {
                const serviceItem = checkbox.closest('.service-item');
                const inputs = serviceItem.querySelectorAll('.service-quantity, .service-price, .service-note, .service-user');
                
                inputs.forEach(input => {
                    if (checkbox.checked) {
                        input.removeAttribute('disabled');
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
                serviceSearch.addEventListener('input', function() {
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

            // Customers + pets from backend with appointment status
            const customers = @json($customers);
            const petsWithAppointments = @json($petsWithAppointments);
            const users = @json($users);
            const options = [];
            const optionsUser = [];
            customers.forEach(customer => {
                (customer.pets || []).forEach(pet => {
                    const hasAppointment = petsWithAppointments.includes(pet.id);
                    options.push({
                        label: `${customer.name} — ${pet.name}`,
                        customer_id: customer.id,
                        pet_id: pet.id,
                        disabled: hasAppointment,
                        hasAppointment: hasAppointment
                    });
                });
            });

            function renderCpResults(items) {
                cpResults.innerHTML = '';
                items.forEach(item => {
                    const a = document.createElement('a');
                    a.href = '#';
                    a.className = `list-group-item list-group-item-action ${item.disabled ? 'text-muted' : ''}`;
                    a.innerHTML = item.label + (item.hasAppointment ? 
                        ' <span class="badge bg-warning float-end">Aktif Randevusu Var</span>' : '');
                    if (item.disabled) {
                        a.style.cursor = 'not-allowed';
                        a.onclick = (e) => e.preventDefault();
                    }
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        hiddenCustomerId.value = item.customer_id;
                        hiddenPetId.value = item.pet_id;
                        cpInput.value = item.label;
                        cpResults.style.display = 'none';
                    });
                    cpResults.appendChild(a);
                });
                cpResults.style.display = items.length ? 'block' : 'none';
            }

            users.forEach(user => {
                optionsUser.push({
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

            function filterOptions(q) {
                console.log(q);
                const needle = q.trim().toLowerCase();
                if (!needle) return options.slice(0, 50);
                return options
                    .filter(o => o.label.toLowerCase().includes(needle))
                    .sort((a, b) => a.disabled - b.disabled) // Disabled items at the end
                    .slice(0, 50);
            }

            if (cpInput) {
                cpInput.addEventListener('input', function () {
                    const cpList = filterOptions(cpInput.value);
                    renderCpResults(cpList);
                });
                cpInput.addEventListener('focus', function () {
                    const cpList = filterOptions(cpInput.value);
                    renderCpResults(cpList);
                });
            }
   
            document.addEventListener('click', function (e) {
                if (!cpResults.contains(e.target) && e.target !== cpInput) {
                    cpResults.style.display = 'none';
                }
            });

            // Restore old selection to input text for UX
            (function restoreOld() {
                const oldCid = hiddenCustomerId.value;
                const oldPid = hiddenPetId.value;
                if (oldCid && oldPid) {
                    const found = options.find(o => String(o.customer_id) === String(oldCid) && String(o.pet_id) === String(oldPid));
                    if (found) cpInput.value = found.label;
                }
            })();
        });
    </script> 
@endpush