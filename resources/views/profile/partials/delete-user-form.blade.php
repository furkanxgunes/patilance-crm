{{-- profile/partials/delete-user-form.blade.php (AdminLTE'ye Çevrilmiş Versiyon) --}}

<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 text-bold">
            {{ __('Hesabı Sil') }}
        </h2>
    </div>
    
    <p class="text-muted">
        {{ __('Hesabınız silindiğinde, tüm kaynakları ve verileri kalıcı olarak silinecektir. Hesabınızı silmeden önce, lütfen saklamak istediğiniz tüm verileri veya bilgileri indirin.') }}
    </p>

    {{-- Modal'ı tetikleyecek buton --}}
    <x-adminlte-button 
        label="{{ __('Hesabı Sil') }}" 
        theme="danger" 
        data-toggle="modal" 
        data-target="#confirm-user-deletion-modal"
    />

    {{-- Onay Modalı --}}
    <x-adminlte-modal id="confirm-user-deletion-modal" title="{{ __('Hesabı Silme Onayı') }}" theme="danger"
        icon="fas fa-trash-alt" class="fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        
        <form method="POST" action="{{ route('profile.destroy') }}" class="p-3">
            @csrf
            @method('delete')
        
            <p class="text-bold">
                {{ __('Hesabınızı kalıcı olarak silmek istediğinizden emin misiniz?') }}
            </p>

            <p class="text-muted">
                {{ __('Hesabınız silindiğinde, tüm kaynakları ve verileri kalıcı olarak silinecektir. Lütfen hesabınızı kalıcı olarak silmek istediğinizi onaylamak için şifrenizi girin.') }}
            </p>

            <div class="form-group mt-4">
                <label for="password" class="sr-only">{{ __('Şifre') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control"
                    placeholder="{{ __('Şifre') }}"
                />
            </div>
            
            {{-- Hata Mesajı --}}
            @if ($errors->userDeletion->has('password'))
                <div class="alert alert-danger mt-2">
                    {{ $errors->userDeletion->first('password') }}
                </div>
            @endif

            <x-slot name="footerSlot">
                <x-adminlte-button theme="secondary" label="{{ __('İptal') }}" data-dismiss="modal" />
                <x-adminlte-button theme="danger" type="submit" label="{{ __('Hesabı Sil') }}" class="ml-auto" />
            </x-slot>

        </form>
    </x-adminlte-modal>
</section>