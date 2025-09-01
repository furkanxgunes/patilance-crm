<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => 'Süheyla Ceylin Kaya', 'email' => 'ceylinkaya3134@gmail.com',   'role' => 'personal'],
            ['name' => 'Ayşegül Bilen',       'email' => 'aysegul.bilgen@yahoo.com.tr','role' => 'personal'],
            ['name' => 'Beyza Nur İzci',      'email' => 'beyza.izci.2020@icloud.com', 'role' => 'personal'],
            ['name' => 'Yasin KAYAN',         'email' => 'kayanyasin423@gmail.com',    'role' => 'personal'],
            ['name' => 'Nazlı Hasret Yılmaz', 'email' => 'nazliy835@gmail.com',        'role' => 'admin'],
            ['name' => 'Furkan Güneş',        'email' => 'gunesfq@gmail.com',          'role' => 'superadmin'],
        ];

        foreach ($users as $u) {
            $name  = trim($u['name']);
            $email = strtolower(trim($u['email']));
            $role  = $u['role'];

            // username kuralı
            $base = $this->generateUsername($name);

            // çakışma çözümü
            $username = $base;
            $i = 2;
            while (User::where('username', $username)->exists()) {
                $username = $base . $i;
                $i++;
            }

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'role' => $role,
                    'password' => Hash::make('patilance123'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    private function generateUsername(string $fullName): string
    {
        $parts = array_values(array_filter(explode(' ', preg_replace('/\s+/', ' ', trim($fullName)))));
        $norm  = fn ($s) => strtolower(Str::ascii($s));

        if (count($parts) >= 3) {
            $firstInitial = mb_substr($norm($parts[0]), 0, 1);
            $second       = $norm($parts[1]);
            $last         = $norm(end($parts));
            return $firstInitial . $second . '.' . $last; // örn: "sceylin.kaya"
        }

        if (count($parts) === 2) {
            return $norm($parts[0]) . '.' . $norm($parts[1]); // örn: "furkan.gunes"
        }

        return $norm($parts[0] ?? 'user');
    }
}
