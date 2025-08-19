<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Kullanıcı listesini gösterir.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Yeni kullanıcı oluşturma formunu gösterir.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Yeni kullanıcıyı kaydeder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,personel'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Belirtilen kullanıcıyı gösterir.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Belirtilen kullanıcıyı düzenleme formunu gösterir.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Belirtilen kullanıcıyı günceller.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,personel'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    /**
     * Belirtilen kullanıcıyı siler.
     */
    public function destroy(User $user)
    {
        // Kendi hesabını silmeyi engelle
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Kullanıcı başarıyla silindi.');
    }
}
