<?php
// database/seeders/WhatsAppTemplatesSeeder.php

namespace Database\Seeders;

use App\Models\WhatsAppTemplate;
use Illuminate\Database\Seeder;

class WhatsAppTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'appointment_scheduled',
                'identifier' => 'appointment_scheduled',
                'category' => 'appointment',
                'content' => 'Sayın {customer_name}, {appointment_date} tarihinde {appointment_time} için randevunuz oluşturulmuştur. Randevu numaranız: #{appointment_id}',
                'variables' => ['customer_name', 'appointment_date', 'appointment_time', 'appointment_id'],
                'is_active' => true
            ]
        ];

        foreach ($templates as $template) {
            WhatsAppTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}