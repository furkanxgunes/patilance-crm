@extends('adminlte::page')

@section('title', 'Personel Yönetimi')

@section('content_header')
    <h1>Personel Yönetimi</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-ban"></i>
                    {{ session('error') }}
                </div>
            @endif
            
            <div class="table-responsive">
                <table id="usersTable" class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Ad Soyad</th>
                            <th>Kullanıcı Adı</th>
                            <th>E-posta</th>
                            <th>Rol</th>
                            <th>Oluşturulma Tarihi</th>
                            <th style="width: 120px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger">Yönetici</span>
                                    @else
                                        <span class="badge bg-primary">Personel</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.edit', $user->id) }}" 
                                           class="btn btn-sm btn-warning" 
                                           title="Düzenle"
                                           data-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id != auth()->id())
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-user" 
                                                    data-id="{{ $user->id }}"
                                                    data-name="{{ $user->name }}"
                                                    title="Sil"
                                                    data-toggle="tooltip">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-sm btn-secondary" 
                                                    disabled
                                                    title="Kendinizi silemezsiniz"
                                                    data-toggle="tooltip">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Silme Onay Modalı -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Kullanıcı Sil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong><span id="userName"></span></strong> isimli kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Sil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('vendor/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
    </style>
@stop

@section('js')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    
    <script>
        $(function () {
            // DataTable ayarları
            $('#usersTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Turkish.json"
                },
                "order": [[0, "desc"]],
                "responsive": true,
                "autoWidth": false,
                "columnDefs": [
                    { "orderable": false, "targets": [6] } // Son sütun sıralanabilir olmasın
                ]
            });
            
            // Tooltip'leri etkinleştir
            $('[data-toggle="tooltip"]').tooltip();
            
            // Silme işlemi için modal ayarları
            $('.delete-user').on('click', function() {
                var userId = $(this).data('id');
                var userName = $(this).data('name');
                var deleteUrl = '{{ url("users") }}/' + userId;
                
                $('#userName').text(userName);
                $('#deleteForm').attr('action', deleteUrl);
                $('#deleteModal').modal('show');
            });
            
            // Başarı/uyarı mesajlarını otomatik kapat
            window.setTimeout(function() {
                $(".alert").fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove(); 
                });
            }, 5000);
        });
    </script>
@stop
