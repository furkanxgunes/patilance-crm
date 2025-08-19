<?php

namespace Database\Factories;

use App\Models\Pet;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pet::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'species' => $this->faker->randomElement(['Köpek', 'Kedi']),
            'breed' => $this->faker->randomElement(['Golden Retriever', 'Tekir', 'Sivas Kangalı']),
            'gender' => $this->faker->randomElement(['Erkek', 'Dişi']),
            'age' => $this->faker->numberBetween(1, 15),
            'weight_kg' => $this->faker->randomFloat(2, 1, 50),
            'medical_notes' => $this->faker->paragraph(),
            'allergies' => $this->faker->sentence(),
            'veterinarian_info' => $this->faker->name() . ' - ' . $this->faker->phoneNumber(),
            'chip_no' => $this->faker->ean8(),
            'customer_id' => \App\Models\Customer::factory(),
        ];
    }
}