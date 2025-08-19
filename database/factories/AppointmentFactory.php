<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Pet;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_id' => Customer::factory(),
            'pet_id' => Pet::factory(),
            'checkin_at' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'notes' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(AppointmentStatus::cases()),
        ];
    }
}