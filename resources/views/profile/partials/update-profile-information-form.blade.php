{{-- profile/partials/update-profile-information-form.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 text-bold">
            {{ __('Profil Bilgileri') }}
        </h2>
    </div>

    <p class="text-muted">
        {{ __('Hesabınızın profil bilgilerini ve e-posta adresini güncelleyin.') }}
    </p>

    {{-- E-posta doğrulama linki için ayrı form --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        {{-- İsim Girişi --}}
        <x-adminlte-input 
            name="name" 
            label="{{ __('Ad Soyad') }}" 
            placeholder="{{ __('Adınızı ve soyadınızı girin') }}"
            :value="old('name', $user->name)" 
            required 
            autocomplete="name"
        />
        @error('name')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror

        {{-- E-posta Girişi --}}
        <x-adminlte-input 
            name="email" 
            type="email" 
            label="{{ __('E-posta') }}" 
            placeholder="{{ __('E-posta adresinizi girin') }}"
            :value="old('email', $user->email)" 
            required 
            autocomplete="username"
        />
        @error('email')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror

        {{-- E-posta Doğrulama Durumu --}}
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning mt-2">
                <p class="mb-0">
                    {{ __('E-posta adresiniz doğrulanmamış.') }}
                    <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline">
                        {{ __('Doğrulama e-postasını yeniden göndermek için tıklayın.') }}
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-weight-bold text-success">
                        {{ __('Yeni bir doğrulama linki e-posta adresinize gönderildi.') }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Kaydetme ve Durum Mesajları --}}
        <div class="d-flex align-items-center mt-3">
            <x-adminlte-button 
                type="submit" 
                label="{{ __('Kaydet') }}" 
                theme="success" 
                icon="fas fa-save" 
            />

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-muted ml-3">
                    {{ __('Kaydedildi.') }}
                </p>
            @endif
        </div>
    </form>
</section>