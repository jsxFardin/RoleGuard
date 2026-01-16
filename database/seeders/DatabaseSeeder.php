<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user if needed (so we can attach roles/permissions in seeders).
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Seed in order (services first as other seeders may depend on them)
        $this->call([
            ServiceSeeder::class,
            BlogCategorySeeder::class,
            StatisticSeeder::class,
            ContactInformationSeeder::class,
            FAQSeeder::class,
            TestimonialSeeder::class,
            BlogPostSeeder::class,
            CaseStudySeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}
