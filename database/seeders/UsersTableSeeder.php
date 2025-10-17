<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name'=>'Admin User',
            'email'=>'admin@example.com',
            'password'=>Hash::make('password'),
            'role'=>'admin'
        ]);

        User::create([
            'name'=>'Worker One',
            'email'=>'worker1@example.com',
            'password'=>Hash::make('password'),
            'role'=>'service_worker'
        ]);

        User::create([
            'name'=>'Resident One',
            'email'=>'resident1@example.com',
            'password'=>Hash::make('password'),
            'role'=>'resident'
        ]);
    }
}
