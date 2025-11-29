<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminSeeder extends Seeder
{
    /**
     * Имя администратора.
     */
    protected string $name = 'Admin';

    /**
     * Email администратора.
     */
    protected string $email = 'admin@mail.com';

    /**
     * Пароль администратора.
     */
    protected string $password = 'password';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Проверяем, существует ли уже администратор с таким email
        $existingAdmin = User::where('email', $this->email)->first();

        if ($existingAdmin) {
            // Обновляем существующего пользователя до роли admin
            $existingAdmin->update([
                'name' => $this->name,
                'role' => UserRole::ADMIN,
                'is_active' => true,
                'password' => Hash::make($this->password),
            ]);

            if ($this->command) {
                $this->command->info("Администратор {$this->email} обновлен.");
            }
        } else {
            // Создаем нового администратора
            User::factory()->admin()->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            if ($this->command) {
                $this->command->info("Администратор {$this->email} создан.");
            }
        }
    }

    /**
     * Установить параметры администратора.
     */
    public function setParameters(string $name, string $email, string $password): self
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;

        return $this;
    }

    /**
     * Установить имя администратора.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Установить email администратора.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Установить пароль администратора.
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
