<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('ciuro.admin_password');
        if (blank($password)) {
            if (app()->environment('production')) throw new RuntimeException('Defina ADMIN_PASSWORD antes de ejecutar el seeder en producción.');
            $password = Str::password(20);
            fwrite(STDOUT, "Contraseña inicial del administrador (guardarla ahora): {$password}".PHP_EOL);
        }
        User::updateOrCreate(['email' => config('ciuro.admin_email')], [
            'name' => config('ciuro.admin_name'), 'password' => Hash::make($password), 'email_verified_at' => now(),
        ]);
    }
}
