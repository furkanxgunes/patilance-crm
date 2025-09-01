<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Breed;

class BreedSeeder extends Seeder
{
    public function run(): void
    {
        $breeds = [
            'Toy Poodle',
            'Chihuahua',
            'Maltese Terrier',
            'Pomeranian Boo',
            'Pug',
            'French Bulldog',
            'Bulldog',
            'American Cocker',
            'English Cocker',
            'Beagle',
            'Havanese',
            'Minyatür Pinscher',
            'Bichon Frise',
            'Maltipoo',
            'Dachshund',
            'Papillon',
            'Shih Tzu',
        ];

        foreach ($breeds as $breed) {
            Breed::firstOrCreate(['name' => $breed]);
        }
    }
}
