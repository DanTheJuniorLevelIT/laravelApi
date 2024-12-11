<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Learner;

class LearnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
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

        // foreach ($barangays as $barangay) {
        //     Learner::factory()
        //         ->count(7) // Adjust the count to evenly distribute (200 / 28 ≈ 7)
        //         ->create(['barangay' => $barangay]);
        // }

        $remainingLearners = 200 - (count($barangays) * 7);
        if ($remainingLearners > 0) {
            Learner::factory()
                ->count($remainingLearners)
                ->create();
        }
    }
}
