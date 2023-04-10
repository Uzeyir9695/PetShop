<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();
        $user->uuid = \Illuminate\Support\Str::uuid();
        $user->first_name = 'John';
        $user->last_name = 'Doe';
        $user->email = 'admin@example.com';
        $user->address = '123 Main St, Anytown, USA';
        $user->avatar = \Illuminate\Support\Str::uuid();
        $user->phone_number = '+1 555-123-4567';
        $user->password = Hash::make('123456');
        $user->save();
    }
}
