{{-- resources/views/segments/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Segment Yönetimi')

@section('content_header')
    <h1>Segment Yönetimi</h1>
@stop

@section('content')
<div class="row">
    {{-- Sol Panel: Segment Listesi --}}
    <div class="col-md-4">
        <x-adminlte-card title="Segmentler" theme="primary" icon="fas fa-layer-group" collapsible>
            <table class="table table-bordered table-hover" id="segments-table">
                <thead>
                    <tr>
                        <th>İsim</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($segments as $segment)
                    <tr data-id="{{ $segment->id }}" class="segment-row">
                        <td class="segment-name">{{ $segment->name }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit-segment">Düzenle</button>
                            @can('delete-core')
                            <form action="{{ route('segments.destroy', $segment) }}" method="POST" class="d-inline delete-segment-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger btn-delete-segment">Sil</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <form action="{{ route('segments.store') }}" method="POST" id="new-segment-form">
                            @csrf
                            <td><input type="text" name="name" class="form-control" placeholder="Yeni segment adı" required></td>
                            <td><button type="submit" class="btn btn-sm btn-success">Ekle</button></td>
                        </form>
                    </tr>
                </tfoot>
            </table>
        </x-adminlte-card>
    </div>

    {{-- Sağ Panel: Hizmet İndirimleri --}}
    <div class="col-md-8">
        <x-adminlte-card title="Düzenlemek İçin Segment Seçin" theme="success" icon="fas fa-percent" collapsible>
            <form action="{{ route('segments.services.update') }}" method="POST" id="segment-services-form">
                @csrf
                <input type="hidden" name="segment_id" id="selected-segment-id" value="">

                <table class="table table-bordered table-hover" id="segment-services-table">
                    <thead>
                        <tr>
                            <th>Hizmet</th>
                            <th>İndirim (%)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                        <tr data-service-id="{{ $service->id }}">
                            <td>{{ $service->name }}</td>
                            <td>
                                <input type="number" name="service_discounts[{{ $service->id }}]" 
                                       class="form-control discount-input" 
                                       step="0.01" min="0" max="100" 
                                       placeholder="0" disabled>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success btn-apply-all">Tümüne Uygula</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success" disabled>İndirimleri Kaydet</button>
                </div>
            </form>
        </x-adminlte-card>
    </div>
</div>
@stop

@section('css')
<style>
    .segment-row { cursor: pointer; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Başta sağ tablo ve buton disabled
    $('#segment-services-form input, #segment-services-form button').prop('disabled', true);

    // Segment seçimi: indirimleri sağa yükle
    $('#segments-table tbody tr').click(function() {
        $('#segments-table tbody tr').removeClass('table-primary');
        $('#segment-services-form input, #segment-services-form button').prop('disabled', true);
        $(this).addClass('table-primary');

        let segmentId = $(this).data('id');
        $('#selected-segment-id').val(segmentId);

        // Tablo başlığı güncelle
        let segmentName = $(this).find('.segment-name').text();
        $('#segment-services-table').closest('.card').find('.card-title').text(segmentName + ' Hizmet İndirimleri');

        // Sağdaki form input ve buttonları aktif et
        $('#segment-services-form input, #segment-services-form button').prop('disabled', false);

        // AJAX ile bu segmentin hizmet indirimlerini çek
        $.get('/segments/' + segmentId + '/services/json', function(data){
            $('#segment-services-table tbody tr').each(function(){
                let serviceId = $(this).data('service-id');
                let discount = data[serviceId] ?? 0;
                $(this).find('.discount-input').val(discount);
            });
        });
    });

    // Segment silme onayı
    $('.btn-delete-segment').click(function(){
        if(confirm('Bu segmenti silmek istediğinize emin misiniz?')) {
            $(this).closest('form').submit();
        }
    });

    // Edit: satırda inline düzenleme
    $('.btn-edit-segment').click(function(){
        let row = $(this).closest('tr');
        let nameTd = row.find('.segment-name');
        let name = nameTd.text().trim();
        nameTd.html('<input type="text" class="form-control" value="'+name+'">');
        $(this).replaceWith('<button class="btn btn-sm btn-success btn-save-segment">Kaydet</button>');
    });

    // Inline segment kaydet
    $(document).on('click', '.btn-save-segment', function(){
        let row = $(this).closest('tr');
        let segmentId = row.data('id');
        let name = row.find('input').eq(0).val();

        $.ajax({
            url: '/segments/' + segmentId,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
            },
            success: function(){
                location.reload();
            }
        });
    });
    $('.btn-apply-all').click(function() {
        let segmentId = $('#selected-segment-id').val();
        let discount = $(this).closest('tr').find('.discount-input').val();
        $('#segment-services-table tbody tr').each(function(){
            $(this).find('.discount-input').val(discount);
        });
    });

});
</script>
@stop
