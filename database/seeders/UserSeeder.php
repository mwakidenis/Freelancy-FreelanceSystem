<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'name' => 'Master Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt("admin"),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'username' => 'client1',
            'name' => 'Sample Client',
            'email' => 'sampleclient1@gmail.com',
            'password' => bcrypt("admin"),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        User::create([
            'username' => 'client2',
            'name' => 'Second Client',
            'email' => 'sampleclient2@gmail.com',
            'password' => bcrypt("admin"),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        User::create([
            'username' => 'freelancer1',
            'name' => 'Sample Freelancer',
            'email' => 'samplefreelancer1@gmail.com',
            'password' => bcrypt("admin"),
            'role' => 'freelancer',
            'email_verified_at' => now(),
        ]);

        User::create([
            'username' => 'freelancer2',
            'name' => 'Second Freelancer',
            'email' => 'samplefreelancer2@gmail.com',
            'password' => bcrypt("admin"),
            'role' => 'freelancer',
            'email_verified_at' => now(),
        ]);
    }
}
