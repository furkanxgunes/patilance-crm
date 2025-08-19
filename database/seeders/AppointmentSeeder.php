<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Pet;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 10 müşteri oluştur
        $customers = Customer::factory()->count(10)->create();

        // 15 evcil hayvan oluştur ve müşterilere ata
        $pets = Pet::factory()->count(15)->make()->each(function ($pet) use ($customers) {
            $pet->customer_id = $customers->random()->id;
            $pet->save();
        });

        // 2025 Eylül ayının başlangıç ve bitiş tarihlerini belirle
        $startDate = Carbon::create(2025, 8, 1);
        $endDate = Carbon::create(2025, 8, 30);

        // Rastgele bir Eylül tarihi oluşturan yardımcı fonksiyon
        $getRandomSeptemberDate = function () use ($startDate, $endDate) {
            return Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));
        };

        // 2 adet tamamlanmış (COMPLETED) randevu oluştur
        Appointment::factory()->count(2)->create([
            'status' => AppointmentStatus::COMPLETED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);

        // Geri kalan 8 adet planlanmış (SCHEDULED) randevu oluştur
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
         Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
         Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
         Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
         Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
         Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
         Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);

        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        Appointment::factory()->count(1)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'customer_id' => $customers->random()->id,
            'pet_id' => $pets->random()->id,
            'checkin_at' => $getRandomSeptemberDate()->toDateString(),
        ]);
        
    }
}