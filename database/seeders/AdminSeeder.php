<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (!$email || !$password) {
            $this->command->warn('ADMIN_EMAIL и ADMIN_PASSWORD не заданы. Администратор не создан.');
            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = env('ADMIN_NAME', 'Administrator');
        $user->save();
        $user->generatePassword($password);
    }
}
