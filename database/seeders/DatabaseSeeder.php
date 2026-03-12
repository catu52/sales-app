<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Default user for testing
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $token = $user->createToken('Test Token')->plainTextToken;

        \App\Models\Client::factory(10)->create();
        \App\Models\Product::factory(10)->create();
        \App\Models\Service::factory(5)->create();
        \App\Models\Service::factory(5)->withDependency()->create();

        $this->command->info("Use the following token for testing: \n" . $token);

    }
}
