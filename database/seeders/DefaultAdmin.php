<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class defaultAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create(
            [
                'username' => 'admin',
                'email' => 'admin@ezone.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'name' => 'Daniel',
                'phone_number' => '0000000',
                'country' => 'germany',
            ]
        );
    }
}
