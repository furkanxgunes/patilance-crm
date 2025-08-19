@extends('adminlte::page')

@section('title', 'Kampanya Düzenle: ' . $campaign->name)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Kampanya Düzenle: {{ $campaign->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Ana Sayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Kampanyalar</a></li>
                <li class="breadcrumb-item"><a href="{{ route('campaigns.show', $campaign) }}">{{ $campaign->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Düzenle</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('campaigns.update', $campaign) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Kampanya Adı <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $campaign->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">Açıklama</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description', $campaign->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Başlangıç Tarihi <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" 
                                   value="{{ old('start_date', $campaign->start_date->format('Y-m-d\TH:i')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">Bitiş Tarihi <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" 
                                   value="{{ old('end_date', $campaign->end_date->format('Y-m-d\TH:i')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_type">İndirim Türü <span class="text-danger">*</span></label>
                            <select class="form-control @error('discount_type') is-invalid @enderror" 
                                    id="discount_type" name="discount_type" required>
                                <option value="percentage" {{ old('discount_type', $campaign->discount_type) === 'percentage' ? 'selected' : '' }}>Yüzde (%)</option>
                                <option value="fixed" {{ old('discount_type', $campaign->discount_type) === 'fixed' ? 'selected' : '' }}>Sabit Tutar (₺)</option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_value">İndirim Değeri <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('discount_value') is-invalid @enderror" 
                                       id="discount_value" name="discount_value" 
                                       value="{{ old('discount_value', $campaign->discount_value) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" id="discount_suffix">
                                        {{ $campaign->discount_type === 'percentage' ? '%' : '₺' }}
                                    </span>
                                </div>
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="max_uses">Maksimum Kullanım (Boş bırakılırsa sınırsız)</label>
                    <input type="number" min="1" class="form-control @error('max_uses') is-invalid @enderror" 
                           id="max_uses" name="max_uses" value="{{ old('max_uses', $campaign->max_uses) }}">
                    @error('max_uses')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $campaign->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Hizmetler <span class="text-danger">*</span></label>
                    <div class="border rounded p-3">
                        @if($services->isEmpty())
                            <div class="alert alert-warning">
                                Henüz hiç hizmet bulunmamaktadır. Önce hizmet ekleyin.
                            </div>
                        @else
                            <div class="mb-3">
                                <input type="text" id="service-search" class="form-control form-control-sm" placeholder="Hizmet ara...">
                            </div>
                            <div id="service-list" style="max-height: 300px; overflow-y: auto;">
                                @php
                                    $selectedServices = old('services', $campaign->services->pluck('id')->toArray());
                                @endphp
                                @foreach($services as $service)
                                    <div class="custom-control custom-checkbox service-item" 
                                         data-service-name="{{ strtolower($service->name) }}">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="service-{{ $service->id }}" 
                                               name="services[]" 
                                               value="{{ $service->id }}"
                                               {{ in_array($service->id, $selectedServices) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="service-{{ $service->id }}">
                                            {{ $service->name }}
                                            <span class="text-muted">({{ $service->base_price }}₺)</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('services')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Güncelle
                    </button>
                    <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        // Update discount suffix based on selected type
        function updateDiscountSuffix() {
            const type = $('#discount_type').val();
            $('#discount_suffix').text(type === 'percentage' ? '%' : '₺');
        }
        
        $('#discount_type').change(updateDiscountSuffix);
        updateDiscountSuffix(); // Initial call
        
        // Service search functionality
        $('#service-search').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.service-item').each(function() {
                const serviceName = $(this).data('service-name');
                if (serviceName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>
@endpush
