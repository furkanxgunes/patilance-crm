@extends('adminlte::master')

@section('body_class', 'login-page')

@section('classes_body', 'login-page')

@section('body')
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <div class="login-logo">
            <img src="{!! config('adminlte.logo_img') !!}" alt="" class="w-50 ">
        </div>
                <!-- <a href="{{ url('/') }}" class="h1"><b>Patilance</b></a> -->
            </div>
            <div class="card-body">
                <p class="login-box-msg">{{ __('Giriş yapmak için bilgilerinizi girin.') }}</p>
                
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="input-group mb-3">
                        <x-text-input 
                            id="login" 
                            class="form-control" 
                            type="text" 
                            name="login" 
                            :value="old('login')" 
                            required 
                            autofocus 
                            autocomplete="username" 
                            placeholder="{{ __('E-posta veya Kullanıcı Adı') }}"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('login')" class="mt-2" />
                    </div>

                    <div class="input-group mb-3">
                        <x-text-input 
                            id="password" 
                            class="form-control"
                            type="password"
                            name="password"
                            required 
                            autocomplete="current-password"
                            placeholder="{{ __('Şifre') }}"
                        />
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" name="remember" id="remember">
                                <label for="remember">
                                    {{ __('Beni Hatırla') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                {{ __('Giriş Yap') }}
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end mt-4">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                                {{ __('Şifrenizi mi unuttunuz?') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop