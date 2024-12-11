<?php

namespace Database\Factories;
use App\Models\Learner;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Learner>
 */
class LearnerFactory extends Factory
{
    protected $model = Learner::class;

    public function definition(): array
    {
        // List of 28 barangays in Sison
        $barangays = [
            'Agat', 'Alibeng', 'Amagbagan', 'Artacho', 'Asan Norte',
            'Asan Sur', 'Bantay Insik', 'Bila', 'Binmeckeg', 'Bulaoen East',
            'Bulaoen West', 'Cabaritan', 'Calunetan', 'Camangaan', 'Cauringan',
            'Dungon', 'Ezperanza', 'Inmalog', 'Kilo', 'Labayug',
            'Paldit', 'Pindangan', 'Pinmilapil', 'Poblacion Central', 'Poblacion Norte',
            'Poblacion Sur', 'Sagunto', 'Tara-Tara',
        ];

        // $barangays = [
        //     'Sison', 'Artacho', 'Asan Sur', 'Binmeckeg', 'Bulaoen East',
        //     'Bulaoen West', 'Cabaritan', 'Calunetan', 'Cauringan',
        //     'Dungon', 'Ezperanza', 'Inmalog', 'Labayug',
        //     'Pindangan', 'Pinmilapil'
        // ];

        $firstNames = [
            'John', 'Jane', 'Robert', 'Emily', 'Michael',
            'Sarah', 'David', 'Heavenson', 'Mirason', 'Heavenson',
            'Jessica', 'Chris', 'Ashley', 'Matthew', 'Sophia',
            'Liam', 'Olivia', 'Noah', 'Emma', 'Ava', 'Wright', 'Scott', 'Torres', 'Nguyen',
            'Hill', 'Flores', 'Green', 'Adams', 'Nelson',
            'Baker', 'Gonzalez', 'Carter', 'Mitchell',
            'Perez', 'Roberts', 'Turner', 'Phillips',
            'Campbell', 'Parker', 'Evans', 'Ramirez', 'James', 'Bennett',
            'Reyes', 'Cruz',  'Sanchez', 'Stewart', 'Morales', 'Mendez',
            'Hernandez', 'Patel', 'Singh', 'Kim', 'Chen', 'Khan',
            'Ali', 'Foster', 'Sanders', 'Hodge', 'Nakamura',
            'OReilly', 'Schmidt'
        ];
        
        $lastNames = [
            'Brown', 'Davis', 'Johnson', 'Sencio', 'Garcia',
            'Miller',
            'White', 'Moore','Torres',
            'Gonzalez','Phillips',
            'Evans','Bennett', 'Singh',
            'Ali',
            'Bishop'
        ];
        
        $middleNames = [
            'A.', 'B.', 'C.', null, 
            'D.', null, 
            null, 
            null, 
            null, 
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        ];

        // $lastName = $this->faker->randomElement($lastNames);
        $lastName = $this->faker->lastName;

        return [
            'lrn' => null,
            'firstname' => $this->faker->randomElement($firstNames),
            'middlename' => $this->faker->randomElement($middleNames),
            'lastname' => $lastName,
            'extension_name' => null,
            'birthdate' => $this->faker->date('Y-m-d', '-10 years'),
            'placeofbirth' => $this->faker->randomElement($barangays),
            'religion' => $this->faker->randomElement(['Catholic', 'Protestant', 'Muslim', 'Others']),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'civil_status' => $this->faker->randomElement(['Single', 'Married', 'Widowed']),
            'last_education' => $this->faker->randomElement(['Kinder', 'Elementary', 'High School']),
            'contact_numbers' => $this->faker->phoneNumber,
            'email' => strtolower($lastName) . '@example.com',
            'password' => bcrypt('password'),
            'image' => null,
        ];
    }
}
