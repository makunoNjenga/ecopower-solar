<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with admin users.
     *
     * @return void
     */
    public function run(): void
    {
        User::create([
            'name'      => 'Micheal Ndegwa',
            'email'     => 'micheal@ecopowertech.co.ke',
            'password'  => Hash::make('password'),
            'phone'     => '+254718308522',
            'is_admin'  => true,
            'is_active' => true,
        ]);
    }
}
