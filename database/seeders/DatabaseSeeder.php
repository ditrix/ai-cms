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
        // User::factory(10)->create();

        // Создание администратора через CreateAdminSeeder
        $this->call(CreateAdminSeeder::class);

        // Создание дополнительного администратора
        $dmitrySeeder = new CreateAdminSeeder;
        $dmitrySeeder->setParameters('Dmitry', 'dmitry@mail.com', 'password');
        $dmitrySeeder->setCommand($this->command);
        $dmitrySeeder->run();

        // Создание тестового пользователя (если не существует)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );
    }
}
