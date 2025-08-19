@extends('adminlte::page')

@section('title', 'Kullanıcıyı Düzenle: ' . $user->name)

@section('content_header')
    <h1>Kullanıcıyı Düzenle: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Ad Soyad <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           placeholder="Ad Soyad" 
                           required 
                           autofocus>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="username">Kullanıcı Adı <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('username') is-invalid @enderror" 
                           id="username" 
                           name="username" 
                           value="{{ old('username', $user->username) }}" 
                           placeholder="Kullanıcı adı" 
                           required
                           readonly>
                    <small class="form-text text-muted">Kullanıcı adı otomatik olarak oluşturulacaktır.</small>
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">E-posta <span class="text-danger">*</span></label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           placeholder="E-posta adresi" 
                           required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın)</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Yeni şifre">
                    <small class="form-text text-muted">Şifreyi değiştirmek istemiyorsanız boş bırakın.</small>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Yeni Şifre Tekrarı</label>
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           placeholder="Yeni şifre tekrarı">
                </div>

                <div class="form-group">
                    <label for="role">Rol <span class="text-danger">*</span></label>
                    <select class="form-control @error('role') is-invalid @enderror" 
                            id="role" 
                            name="role" 
                            required>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Yönetici</option>
                        <option value="personel" {{ old('role', $user->role) == 'personel' ? 'selected' : '' }}>Personel</option>
                    </select>
                    @error('role')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group text-right">
                    <a href="{{ route('users.index') }}" class="btn btn-default mr-2">
                        <i class="fas fa-arrow-left"></i> İptal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 0.2rem rgba(60, 141, 188, 0.25);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Kullanıcı adı otomatik oluşturma
            function generateUsername(name) {
                return name
                    .toLowerCase()
                    .replace(/[^a-z0-9ğüşıöç\s]/g, '') // Özel karakterleri kaldır
                    .replace(/\s+/g, '.') // Boşlukları noktaya çevir
                    .replace(/\.+/g, '.') // Ardışık noktaları tek noktaya çevir
                    .replace(/\.$/, '') // Sondaki noktayı kaldır
                    .replace(/[ğg]/g, 'g')
                    .replace(/[üu]/g, 'u')
                    .replace(/[şs]/g, 's')
                    .replace(/[ıi]/g, 'i')
                    .replace(/[öo]/g, 'o')
                    .replace(/[çc]/g, 'c');
            }

            // İsim değiştiğinde kullanıcı adını güncelle
            $('#name').on('input', function() {
                let name = $(this).val().trim();
                if (name) {
                    $('#username').val(generateUsername(name));
                } else {
                    $('#username').val('');
                }
            });

            // Sayfa yüklendiğinde kullanıcı adı yoksa oluştur
            @if(empty($user->username))
                let name = $('#name').val().trim();
                if (name) {
                    $('#username').val(generateUsername(name));
                }
            @endif
        });
    </script>
@stop
