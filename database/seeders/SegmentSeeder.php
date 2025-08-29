<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Segment;

class SegmentSeeder extends Seeder
{
    public function run(): void
    {
        $segments = [
            [
                'name' => 'VIP',
                'icon' => 'fas fa-crown', // FontAwesome ikon
            ],
            [
                'name' => 'Sadık Müşteri',
                'icon' => 'fas fa-heart',
            ],
            [
                'name' => 'Yeni Müşteri',
                'icon' => 'fas fa-user-plus',
            ],
            [
                'name' => 'Potansiyel',
                'icon' => 'fas fa-seedling',
            ],
        ];

        foreach ($segments as $segment) {
            Segment::firstOrCreate(['name' => $segment['name']], $segment);
        }
    }
}
