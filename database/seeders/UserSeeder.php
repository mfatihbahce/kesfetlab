<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin kullanıcısı
        User::create([
            'name' => 'Admin',
            'email' => 'admin@kesfetlab.com',
            'phone' => '5551234567',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'is_active' => true,
        ]);

        // Eğitmen kullanıcıları
        $instructors = [
            [
                'name' => 'Ahmet Yılmaz',
                'email' => 'ahmet.yilmaz@kesfetlab.com',
                'phone' => '5551234568',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'first_name' => 'Ahmet',
                'last_name' => 'Yılmaz',
                'profession' => 'Bilgisayar Öğretmeni',
                'is_active' => true,
            ],
            [
                'name' => 'Ayşe Demir',
                'email' => 'ayse.demir@kesfetlab.com',
                'phone' => '5551234569',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'first_name' => 'Ayşe',
                'last_name' => 'Demir',
                'profession' => 'Sanat Öğretmeni',
                'is_active' => true,
            ],
            [
                'name' => 'Mehmet Kaya',
                'email' => 'mehmet.kaya@kesfetlab.com',
                'phone' => '5551234570',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'first_name' => 'Mehmet',
                'last_name' => 'Kaya',
                'profession' => 'Fen Bilgisi Öğretmeni',
                'is_active' => true,
            ],
            [
                'name' => 'Fatma Özkan',
                'email' => 'fatma.ozkan@kesfetlab.com',
                'phone' => '5551234571',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'first_name' => 'Fatma',
                'last_name' => 'Özkan',
                'profession' => 'Müzik Öğretmeni',
                'is_active' => true,
            ],
        ];

        foreach ($instructors as $instructor) {
            User::create($instructor);
        }
    }
}
