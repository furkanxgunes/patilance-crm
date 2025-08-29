<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'username' => 'nullable|string|max:255|regex:/^[a-z0-9.]+$/|unique:users,username',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (empty($request->username) && !empty($request->name)) {
            $request->username = $this->generateUsername($request->name);
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
    public function generateUsername($name)
{
    $username = mb_strtolower($name, 'UTF-8');

    // Özel Türkçe karakterleri dönüştür
    $replacements = [
        'ğ' => 'g', 'Ğ' => 'g',
        'ü' => 'u', 'Ü' => 'u',
        'ş' => 's', 'Ş' => 's',
        'ı' => 'i', 'İ' => 'i',
        'ö' => 'o', 'Ö' => 'o',
        'ç' => 'c', 'Ç' => 'c',
    ];
    $username = strtr($username, $replacements);

    // Harf, rakam ve boşluk dışındakileri temizle
    $username = preg_replace('/[^a-z0-9\s]/', '', $username);

    // Boşlukları noktaya çevir
    $username = preg_replace('/\s+/', '.', $username);

    // Birden fazla noktayı teke indir
    $username = preg_replace('/\.+/', '.', $username);

    // Sondaki noktayı sil
    $username = rtrim($username, '.');

    return $username;
}
}
