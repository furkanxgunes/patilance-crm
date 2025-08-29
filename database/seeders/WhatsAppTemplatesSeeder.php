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
                'content' => "Sayın ```{{1}}, ```
Patilance'de ```{{2}}``` için``` {{3}}``` randevunuz *planlandı*. 💙

📅 Tarih: ```{{4}}```

Sizi ve sevimli dostumuzu bekliyoruz 🐶

Randevunuzdan *15 dakika önce* salonda bulunmanızı rica ederiz. 
Gecikme durumunda *randevunuzun saatinde değişiklik veya iptali gerekebilir.*

📱Randevunuzu iptal etmek veya farklı bir işlem için *+90(538) 874 09 84* numaralı telefondan bize ulaşabilirsiniz.",
                'variables' => ['customer_name', 'pet_name', 'services', 'appointment_date'],
                'is_active' => true
            ],
            [
                'name' => 'appointment_checked_in',
                'identifier' => 'appointment_checked_in',
                'category' => 'appointment',
                'content' => "Merhaba ```{{1}}```,  

```{{2}}``` başarıyla Patilance’a giriş yaptı! 🐾  

Ekibimiz onun konforu ve güvenliği için hazır. 💙  

Teslim Tutanağına ilettiğimiz PDF üzerinden ulaşabilirsiniz.

Herhangi bir sorunuz için  *+90(538) 874 09 84* numaralı telefondan bize ulaşabilirsiniz.

Patilance ailesi olarak {{3}}’i ağırlamaktan mutluluk duyuyoruz! 🐶🐱",
                'variables' => ['customer_name', 'pet_name', 'services'],
                'is_active' => true 
            ],
            [
                'name' => 'appointment_checked_in',
                'identifier' => 'appointment_checked_in',
                'category' => 'appointment',
                'content' => "Merhaba ```{{1}}```,  

```{{2}}``` başarıyla Patilance’a giriş yaptı! 🐾  

Ekibimiz onun konforu ve güvenliği için hazır. 💙  

Teslim Tutanağına ilettiğimiz PDF üzerinden ulaşabilirsiniz.

Herhangi bir sorunuz için  *+90(538) 874 09 84* numaralı telefondan bize ulaşabilirsiniz.

Patilance ailesi olarak {{3}}’i ağırlamaktan mutluluk duyuyoruz! 🐶🐱",
                'variables' => ['customer_name', 'pet_name', 'services', 'appointment_date'],
                'is_active' => true
            ],
            [
                'name' => 'appointment_completed',
                'identifier' => 'appointment_completed',
                'category' => 'appointment',
                'content' => "Merhaba {{1}} 🖐️

{{2}} başarıyla Patilance’dan ayrıldı! 🐾
Umarız konforlu ve keyifli bir deneyim yaşadı. 💙

PDF’i kontrol ederek hizmet detaylarına ulaşabilirsiniz. 📄

Herhangi bir sorunuz olursa: +90 (538) 874 09 84 numaralı telefondan bizimle iletişime geçebilirsiniz.

Patilance ailesi olarak {{3}}’i tekrar ağırlamayı dört gözle bekliyoruz! 🐶🐱",
                'variables' => ['customer_name', 'pet_name', 'pet_name'],
                'is_active' => true
            ],
        ];

        foreach ($templates as $template) {
            WhatsAppTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}