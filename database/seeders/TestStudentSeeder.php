<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;

class TestStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'first_name' => 'Ahmet',
                'last_name' => 'Yılmaz',
                'tc_identity' => '12345678901',
                'birth_date' => '2015-03-15',
                'address' => 'Atatürk Mahallesi, Cumhuriyet Caddesi No:123, Ankara',
                'health_condition' => 'Alerjik astım',
                'parent_first_name' => 'Mehmet',
                'parent_last_name' => 'Yılmaz',
                'parent_phone' => '5551234567',
                'parent_email' => 'mehmet.yilmaz@email.com',
                'parent_profession' => 'Mühendis',
                'emergency_contact_name' => 'Fatma Yılmaz',
                'emergency_contact_phone' => '5551234568',
                'emergency_contact_relation' => 'Anne',
                'registration_status' => 'pending',
            ],
            [
                'first_name' => 'Ayşe',
                'last_name' => 'Demir',
                'tc_identity' => '12345678902',
                'birth_date' => '2016-07-22',
                'address' => 'Yeni Mahalle, İstiklal Sokak No:45, İstanbul',
                'health_condition' => null,
                'parent_first_name' => 'Ali',
                'parent_last_name' => 'Demir',
                'parent_phone' => '5551234569',
                'parent_email' => 'ali.demir@email.com',
                'parent_profession' => 'Öğretmen',
                'emergency_contact_name' => 'Zeynep Demir',
                'emergency_contact_phone' => '5551234570',
                'emergency_contact_relation' => 'Anne',
                'registration_status' => 'approved',
            ],
            [
                'first_name' => 'Mehmet',
                'last_name' => 'Kaya',
                'tc_identity' => '12345678903',
                'birth_date' => '2014-11-08',
                'address' => 'Gazi Mahallesi, Kurtuluş Caddesi No:78, İzmir',
                'health_condition' => 'Görme bozukluğu (gözlük kullanıyor)',
                'parent_first_name' => 'Hasan',
                'parent_last_name' => 'Kaya',
                'parent_phone' => '5551234571',
                'parent_email' => 'hasan.kaya@email.com',
                'parent_profession' => 'Doktor',
                'emergency_contact_name' => 'Elif Kaya',
                'emergency_contact_phone' => '5551234572',
                'emergency_contact_relation' => 'Anne',
                'registration_status' => 'pending',
            ],
            [
                'first_name' => 'Fatma',
                'last_name' => 'Özkan',
                'tc_identity' => '12345678904',
                'birth_date' => '2017-01-30',
                'address' => 'Cumhuriyet Mahallesi, Barış Sokak No:12, Bursa',
                'health_condition' => null,
                'parent_first_name' => 'Mustafa',
                'parent_last_name' => 'Özkan',
                'parent_phone' => '5551234573',
                'parent_email' => 'mustafa.ozkan@email.com',
                'parent_profession' => 'Avukat',
                'emergency_contact_name' => 'Selin Özkan',
                'emergency_contact_phone' => '5551234574',
                'emergency_contact_relation' => 'Anne',
                'registration_status' => 'rejected',
            ],
            [
                'first_name' => 'Ali',
                'last_name' => 'Çelik',
                'tc_identity' => '12345678905',
                'birth_date' => '2015-09-14',
                'address' => 'Yenişehir Mahallesi, Atatürk Caddesi No:56, Antalya',
                'health_condition' => 'Dikkat eksikliği',
                'parent_first_name' => 'İbrahim',
                'parent_last_name' => 'Çelik',
                'parent_phone' => '5551234575',
                'parent_email' => 'ibrahim.celik@email.com',
                'parent_profession' => 'Mimar',
                'emergency_contact_name' => 'Hatice Çelik',
                'emergency_contact_phone' => '5551234576',
                'emergency_contact_relation' => 'Anne',
                'registration_status' => 'pending',
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}
