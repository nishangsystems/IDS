<?php

use App\Models\School;
use App\Models\Students;
use App\Models\User;
use Database\Seeders\BatchSeeder;
use Database\Seeders\ClassSubjectSeeder;
use Database\Seeders\SchoolUnitSeeder;
use Database\Seeders\StudentClassSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\RolesPermissionSeeder;
use Database\Seeders\UserRoleSeeder;
use Database\Seeders\UserPermissionSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Students::create([
            'name'=>'TEST STUDENT',
            'email'=>'teststudent@gmail.com',
            'matric'=>'BTS56A723',
            'gender'=>'male',
            'dob'=>'1990-04-23',
            'pob'=>'Bituja Kaari',
            'campus'=>'Campustee',
            'password'=>Hash::make('12345678'),
            'school_id'=>1,
            'program'=>'Integrated Course Structures',
        ]);

        User::create([
            'name'=>'Admin User',
            'email'=>'admin@gmail.com',
            'username'=>'admin',
            'gender'=>'male',
            'phone'=>'237672908239',
            'address'=>'BEEEZEC Junction',
            'password'=>Hash::make('password'),
        ]);

        School::create([
            'name'=>'OUR SCHOOL',
            'contact'=>'237672908239',
            'address'=>'our official school address'
        ]);
    }
}
