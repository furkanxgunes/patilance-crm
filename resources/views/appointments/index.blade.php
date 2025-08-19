@extends('adminlte::page')

@section('title', 'Randevular')

@section('content_header')
    <div class="mb-3">
        <h1 class="mb-2">Randevu Yönetimi</h1>
        @php
            $breadcrumbs = [
                route('dashboard') => 'Ana Sayfa',
                '' => 'Randevular'
            ];
        @endphp
        @include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
    </div>
@stop

@section('content')
    <style>
        /* Simple pagination styles */
        .pagination {
            justify-content: center;
            margin: 1rem 0;
        }
        
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            min-width: 32px;
            text-align: center;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #3490dc;
            border-color: #3490dc;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card title="Randevu Listesi" theme="info" icon="fas fa-calendar-alt" collapsible>

                {{-- Başarı ve Hata Mesajları --}}
                @if (session('success'))
                    <x-adminlte-alert theme="success" title="Başarılı">
                        {{ session('success') }}
                    </x-adminlte-alert>
                @endif
                @if (session('error'))
                    <x-adminlte-alert theme="danger" title="Hata">
                        {{ session('error') }}
                    </x-adminlte-alert>
                @endif

                <div class="d-flex justify-content-between mb-3">
                    <form method="GET" action="{{ route('appointments.index') }}" class="form-inline">
                        <div class="input-group mr-2">
                            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Müşteri, pet adı veya #ID">
                        </div>
                        <div class="input-group mr-2">
                            <select name="status" class="form-control">
                                <option value="">Durum (tümü)</option>
                                @foreach(($statuses ?? []) as $st)
                                    <option value="{{ $st->value }}" {{ ($status ?? '')===$st->value ? 'selected' : '' }}>{{ __($st->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-light ml-2">Temizle</a>
                    </form>
                    <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Randevu Ekle
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Müşteri</th>
                                <th>Evcil Hayvan</th>
                                <th>Check-in</th>
                                <th>Durum</th>
                                <th style="width: 250px;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appointments as $appointment)
                                <tr>
                                    <td>
                                        @if ($appointment->customer)
                                            <a href="{{ route('customers.show', $appointment->customer->id) }}">
                                                {{ $appointment->customer->name }}
                                            </a>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($appointment->pet)
                                            <a href="{{ route('pets.show', $appointment->pet->id) }}">
                                                {{ $appointment->pet->name }}
                                            </a>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->checkin_at)->format('d.m.Y') }}</td>
                                    <td>
                                        @php
                                            $theme = 'secondary';
                                            if ($appointment->status === \App\Enums\AppointmentStatus::SCHEDULED) $theme = 'info';
                                            if ($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN) $theme = 'warning';
                                            if ($appointment->status === \App\Enums\AppointmentStatus::COMPLETED) $theme = 'success';
                                            if ($appointment->status === \App\Enums\AppointmentStatus::CANCELLED) $theme = 'danger';
                                        @endphp
                                        <span class="badge badge-{{ $theme }}">
                                            {{ __( $appointment->status->value) }}
                                        </span>
                                    </td>
                                    <td class="text-center d-flex justify-content-around align-items-center">
                                        @if ($appointment->status === \App\Enums\AppointmentStatus::SCHEDULED)
                                            <a href="{{ route('appointments.checkin.form', $appointment) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-sign-in-alt"></i> Check-in
                                            </a>
                                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-primary ml-2"><i class="fas fa-edit"></i> Düzenle</a>
                                            <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline ml-2" onsubmit="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Sil</button>
                                            </form>
                                        @elseif ($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN)
                                            <a href="{{ route('appointments.checkout.form', $appointment) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-sign-out-alt"></i> Check-out
                                            </a>
                                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-warning ml-2"><i class="fas fa-cut"></i> Hizmet Ekle/Düzenle</a>
                                        @else
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> Görüntüle</a>
                                        @endif
                                        @if ($appointment->status === \App\Enums\AppointmentStatus::CHECKED_IN)
                                            <a href="{{ route('appointments.delivery.pdf', $appointment) }}" class="btn btn-sm btn-outline-secondary ml-2" target="_blank">
                                                <i class="fas fa-file-pdf"></i> Teslim Tutanağı
                                            </a>
                                        @elseif ($appointment->status === \App\Enums\AppointmentStatus::COMPLETED)
                                            <a href="{{ route('appointments.pdf', $appointment) }}" class="btn btn-sm btn-outline-secondary ml-2" target="_blank">
                                                <i class="fas fa-file-pdf"></i> Randevu PDF
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Henüz hiç randevu oluşturulmamış.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($appointments->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $appointments->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('js') {{-- @push('scripts') yerine @section('js') kullanıldı --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper function to format date for datetime-local input
            const getFormattedDateTime = () => {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                return now.toISOString().slice(0, 16);
            };

            // Check-in logic
            document.querySelectorAll('.checkin-btn').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const formId = this.dataset.formId;
                    const customerName = this.dataset.customerName;
                    const petName = this.dataset.petName;

                    Swal.fire({
                        title: 'Check-in Onayı',
                        html: `
                            <div class="text-left">
                                <p><strong>Müşteri:</strong> ${customerName}</p>
                                <p><strong>Evcil Hayvan:</strong> ${petName}</p>
                                <hr class="my-3">
                                <label for="swal-checkin-time" class="block text-sm font-medium text-gray-700 mt-4">Check-in Tarih ve Saati:</label>
                                <input id="swal-checkin-time" type="datetime-local" class="swal2-input w-full" value="${getFormattedDateTime()}">
                            </div>
                        `,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Check-in Yap',
                        cancelButtonText: 'İptal',
                        preConfirm: () => {
                            const time = document.getElementById('swal-checkin-time').value;
                            if (!time) {
                                Swal.showValidationMessage('Lütfen bir tarih ve saat girin.');
                            }
                            return time;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById(formId);
                            form.querySelector('.checkin-time-input').value = result.value;
                            form.submit();
                        }
                    });
                });
            });

            // Check-out logic
            document.querySelectorAll('.checkout-btn').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const formId = this.dataset.formId;
                    const customerName = this.dataset.customerName;
                    const petName = this.dataset.petName;
                    const services = JSON.parse(this.dataset.services);
                    let servicesHtml = services.length > 0 ? '<ul>' + services.map(s => `<li class="ml-4 list-disc">${s}</li>`).join('') + '</ul>' : '<p>Hizmet seçilmemiş.</p>';

                    Swal.fire({
                        title: 'Check-out Onayı',
                        html: `
                            <div class="text-left">
                                <p><strong>Müşteri:</strong> ${customerName}</p>
                                <p><strong>Evcil Hayvan:</strong> ${petName}</p>
                            <p class="mt-2"><strong>Alınan Hizmetler:</strong></p>
                            ${servicesHtml}
                            <hr class="my-3">
                            <label for="swal-checkout-time" class="block text-sm font-medium text-gray-700 mt-4">Check-out Tarih ve Saati:</label>
                            <input id="swal-checkout-time" type="datetime-local" class="swal2-input w-full" value="${getFormattedDateTime()}">
                        </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Check-out Yap ve Tamamla',
                        cancelButtonText: 'İptal',
                        preConfirm: () => {
                            const time = document.getElementById('swal-checkout-time').value;
                            if (!time) {
                                Swal.showValidationMessage('Lütfen bir tarih ve saat girin.');
                            }
                            return time;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById(formId);
                            form.querySelector('.checkout-time-input').value = result.value;
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@stop