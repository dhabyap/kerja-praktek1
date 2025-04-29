<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\unit;
use App\Models\User;
use App\Models\Level;
use App\Models\Appartement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {




        Appartement::insert([
            [
                'id' => 1,
                'nama' => 'The Suites Metro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nama' => 'Grand Asia Afrika',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nama' => 'Gateway Pasteur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nama' => 'Jardin Cihampelas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Level::insert([
            [
                'id' => 1,
                'nama' => 'Super Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nama' => 'Admin Global',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nama' => 'Admin Lokal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Unit::insert([
            [
                'id' => 1,
                'nama' => 'D09-21',
                'appartement_id' => 1,
            ],
            [
                'id' => 2,
                'nama' => 'test',
                'appartement_id' => 1,
            ],
            [
                'id' => 3,
                'nama' => 'Jardin Cihampelas',
                'appartement_id' => 1,
            ],
        ]);



        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'level_id' => 1,
        ]);

        User::create([
            'name' => 'Admin Global',
            'email' => 'adminglobal@example.com',
            'password' => Hash::make('password'),
            'level_id' => 2,
        ]);

        User::create([
            'name' => 'Admin Lokal',
            'email' => 'adminlokal@example.com',
            'password' => Hash::make('password'),
            'level_id' => 3,
        ]);


    }
}
