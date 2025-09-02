@extends('adminlte::page')

@section('title', 'Irk Yönetimi')

@section('content_header')
    <h1>Irk Yönetimi</h1>
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
    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary" id="addBreedBtn">
                <i class="fas fa-plus"></i> Yeni Ekle
            </button>
            
        </div>
        
        <div class="card-body">
            <!-- Add/Edit Form (Initially Hidden) -->
            <div class="card card-primary d-none" id="breedFormContainer">
                <div class="card-header">
                    <h3 class="card-title" id="formTitle">Yeni Irk Ekle</h3>
                </div>
                <form id="breedForm">
                    @csrf
                    <input type="hidden" id="breedId">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Irk Adı <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" id="submitSpinner"></span>
                            <span id="submitText">Kaydet</span>
                        </button>
                        <button type="button" class="btn btn-default" id="cancelBtn">İptal</button>
                    </div>
                </form>
            </div>

            <!-- Breeds Table -->
            <div class="table-responsive">
            <form method="GET" action="{{ route('breeds.index') }}" class="form-inline">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="İsim, e-posta, telefon ara...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        <a href="{{ route('breeds.index') }}" class="btn btn-outline-light">Temizle</a>
                    </div>
                </div>
            </form>
                <table class="table table-bordered table-striped mt-2">
                    <thead>
                        <tr>
                            <th>Irk Adı</th>
                            <th style="width: 120px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="breedsTableBody">
                        @forelse($breeds as $breed)
                            <tr id="breed-{{ $breed->id }}">
                                <td>{{ $breed->name }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning edit-breed" data-id="{{ $breed->id }}" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @can('delete-core')
                                        <button class="btn btn-sm btn-danger delete-breed" data-id="{{ $breed->id }}" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Henüz kayıtlı ırk bulunmamaktadır.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($breeds->hasPages())
            <div class="mt-3 d-flex justify-content-center">
                {{ $breeds->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        @endif
        </div>
    </div>
@stop

@push('js')
<script>
$(function () {
    // Show/Hide form
    $('#addBreedBtn').on('click', function() {
        $('#breedFormContainer').removeClass('d-none');
        resetForm();
        $('html, body').animate({
            scrollTop: $('#breedFormContainer').offset().top - 20
        }, 500);
    });

    // Form submit handler
    $('#breedForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $('#submitBtn');
        const $submitText = $('#submitText');
        const $submitSpinner = $('#submitSpinner');
        
        // Show loading state
        $submitBtn.prop('disabled', true);
        $submitText.addClass('d-none');
        $submitSpinner.removeClass('d-none');
        
        const formData = $form.serialize();
        const breedId = $('#breedId').val();
        const url = breedId ? `/breeds/${breedId}` : '/breeds';
        const method = breedId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                if (response.success) {
                    resetForm();
                    if (method === 'POST') {
                        Swal.fire(
                                'Başarılı!',
                                'Irk başarıyla eklendi!',
                                'success'
                            );
                        addBreedToTable(response.breed);
                    } else {
                        Swal.fire(
                                'Başarılı!',
                                'Irk başarıyla güncellendi!',
                                'success'
                            );
                        updateBreedInTable(response.breed);
                    }
                    $('#breedFormContainer').addClass('d-none');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');
                    
                    $.each(errors, function(key, value) {
                        const $input = $(`#${key}`);
                        $input.addClass('is-invalid');
                        $input.next('.invalid-feedback').text(value[0]);
                    });
                }
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
                $submitText.removeClass('d-none');
                $submitSpinner.addClass('d-none');
            }
        });
    });

    // Edit breed
    $(document).on('click', '.edit-breed', function() {
        const breedId = $(this).data('id');
        const $row = $(`#breed-${breedId}`);
        
        $('#breedId').val(breedId);
        $('#name').val($row.find('td:eq(0)').text());
        $('#description').val($row.find('td:eq(1)').text() === '-' ? '' : $row.find('td:eq(1)').text());
        
        $('#formTitle').text('Irk Düzenle');
        $('#submitText').text('Güncelle');
        $('#breedFormContainer').removeClass('d-none');
        
        $('html, body').animate({
            scrollTop: $('#breedFormContainer').offset().top - 20
        }, 500);
    });

    // Cancel edit
    $('#cancelBtn').on('click', function() {
        resetForm();
        $('#breedFormContainer').addClass('d-none');
    });

    // Delete breed
    $(document).on('click', '.delete-breed', function() {
        const breedId = $(this).data('id');
        const breedName = $(this).closest('tr').find('td:first').text();
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        Swal.fire({
            title: 'Emin misiniz?',
            text: `"${breedName}" isimli ırkı silmek istediğinize emin misiniz?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: `/breeds/${breedId}`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify({
                        _method: 'DELETE',
                        _token: csrfToken
                    }),
                    success: function(response) {
                        if (response && response.success) {
                            $(`#breed-${breedId}`).fadeOut(300, function() {
                                $(this).remove();
                                if ($('#breedsTableBody tr').length === 0) {
                                    $('#breedsTableBody').html('<tr><td colspan="2" class="text-center">Henüz kayıtlı ırk bulunmamaktadır.</td></tr>');
                                }
                            });
                        } else {
                            Swal.fire(
                                'Hata!',
                                'Bu Irk Silinemiyor, Tanımlı Olduğu Bir Evcil Hayvan Olabilir.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'Silme işlemi sırasında bir hata oluştu.';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                        }
                        
                        Swal.fire(
                            'Hata!',
                            'Lütfen Sayfayı Yenileyin ve Tekrar Deneyin',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Add new breed to table
    function addBreedToTable(breed) {
        const newRow = `
            <tr id="breed-${breed.id}">
                <td>${breed.name}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-warning edit-breed" data-id="${breed.id}" title="Düzenle">
                        <i class="fas fa-edit"></i>
                    </button>
                    @can('delete-core')
                        <button class="btn btn-sm btn-danger delete-breed" data-id="${breed.id}" title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endcan
                </td>
            </tr>
        `;
        
        if ($('#breedsTableBody tr:first-child').hasClass('no-data')) {
            $('#breedsTableBody').html(newRow);
        } else {
            $('#breedsTableBody').prepend(newRow);
        } 
    }
    
    // Update breed in table
    function updateBreedInTable(breed) {
        const $row = $(`#breed-${breed.id}`);
        $row.find('td:eq(0)').text(breed.name);
    }
    
    // Reset form
    function resetForm() {
        $('#breedForm')[0].reset();
        $('#breedId').val('');
        $('#formTitle').text('Yeni Irk Ekle');
        $('#submitText').text('Kaydet');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
});
</script>
@endpush
