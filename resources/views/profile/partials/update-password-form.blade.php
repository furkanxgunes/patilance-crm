{{-- profile/partials/update-password-form.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 text-bold">
            {{ __('Şifreyi Güncelle') }}
        </h2>
    </div>

    <p class="text-muted">
        {{ __('Hesabınızın güvende kalması için uzun, rastgele bir şifre kullandığınızdan emin olun.') }}
    </p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        {{-- Mevcut Şifre --}}
        <x-adminlte-input 
            name="current_password" 
            type="password" 
            label="{{ __('Mevcut Şifre') }}" 
            placeholder="{{ __('Mevcut şifrenizi girin') }}"
            autocomplete="current-password"
        />
        @error('current_password', 'updatePassword')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror

        {{-- Yeni Şifre --}}
        <x-adminlte-input 
            name="password" 
            type="password" 
            label="{{ __('Yeni Şifre') }}" 
            placeholder="{{ __('Yeni şifrenizi girin') }}"
            autocomplete="new-password"
        />
        @error('password', 'updatePassword')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror

        {{-- Yeni Şifre Tekrar --}}
        <x-adminlte-input 
            name="password_confirmation" 
            type="password" 
            label="{{ __('Şifre Tekrar') }}" 
            placeholder="{{ __('Yeni şifrenizi tekrar girin') }}"
            autocomplete="new-password"
        />
        @error('password_confirmation', 'updatePassword')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror

        {{-- Kaydetme Butonu ve Durum Mesajı --}}
        <div class="d-flex align-items-center mt-3">
            <x-adminlte-button 
                type="submit" 
                label="{{ __('Kaydet') }}" 
                theme="success" 
                icon="fas fa-save" 
            />
            
            @if (session('status') === 'password-updated')
                <p class="text-sm text-muted ml-3">
                    {{ __('Kaydedildi.') }}
                </p>
            @endif
        </div>
    </form>
</section>