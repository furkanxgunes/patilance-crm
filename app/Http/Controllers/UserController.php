<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Kullanıcı listesini gösterir.
     */
    public function index()
    {
        $users = \App\Models\User::query()
        ->where(function ($q) {
            $q->where('role', '!=', 'superadmin')
              ->orWhereNull('role'); // role null ise yine gözüksün
        })
        ->latest('id')
        ->paginate(20)
        ->withQueryString();

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,personel'],
        ]);
        
        $username = $this->generateUsername($validated['name']);
        $base = $username;
        $i = 2;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }

        User::create([
            'name' => $validated['name'],
            'username' => $username,
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
            // normalize ederek karşılaştır (boşluk/case farklılıklarını görmezden gel)
    $newName = preg_replace('/\s+/', ' ', trim($validated['name']));
    $oldName = preg_replace('/\s+/', ' ', trim((string)$user->name));
     // SADECE isim değiştiyse username’i yeniden üret
     if (mb_strtolower($newName) !== mb_strtolower($oldName)) {
        // kuralın: "sceylin.kaya" / "furkan.gunes"
        $base = $this->generateUsername($newName);

        // inline çakışma çözümü (mevcut kullanıcı hariç)
        $username = $base;
        $i = 2;
        while (
            User::where('username', $username)
                ->where('id', '!=', $user->id)
                ->exists()
        ) {
            $username = $base . $i;
            $i++;
        }
        $user->username = $username;
    }
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $user->username,
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
    private function generateUsername(string $fullName): string
{
    $parts = array_values(array_filter(explode(' ', preg_replace('/\s+/', ' ', trim($fullName)))));
    $norm = fn ($s) => strtolower(Str::ascii($s));

    if (count($parts) >= 3) {
        $firstInitial = mb_substr($norm($parts[0]), 0, 1);
        $second = $norm($parts[1]);
        $last = $norm(end($parts));
        return $firstInitial . $second . '.' . $last;
    }

    if (count($parts) === 2) {
        $first = $norm($parts[0]);
        $last  = $norm($parts[1]);
        return $first . '.' . $last;
    }

    return $norm($parts[0] ?? 'user');
}

}
