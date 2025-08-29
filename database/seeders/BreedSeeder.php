<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Breed;

class BreedSeeder extends Seeder
{
    public function run(): void
    {
        $breeds = [
            'Golden Retriever',
            'Pomeranian',
            'Kangal',
            'German Shepherd',
            'Bulldog',
            'Labrador',
            'Poodle',
            'Chihuahua',
        ];

        foreach ($breeds as $breed) {
            Breed::firstOrCreate(['name' => $breed]);
        }
    }
}
