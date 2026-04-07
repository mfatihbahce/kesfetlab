<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Workshop;

class WorkshopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workshops = [
            [
                'name' => 'Robotik ve Kodlama',
                'description' => 'Çocukların algoritma düşünme becerilerini geliştiren, robotik ve programlama temellerini öğreten atölye.',
                'capacity' => 15,
                'price' => 1200.00,
                'status' => 'active',
            ],
            [
                'name' => 'Sanat ve Tasarım',
                'description' => 'Yaratıcılığı destekleyen, farklı sanat tekniklerini öğreten ve tasarım becerilerini geliştiren atölye.',
                'capacity' => 20,
                'price' => 800.00,
                'status' => 'active',
            ],
            [
                'name' => 'Bilim ve Deney',
                'description' => 'Bilimsel düşünme becerilerini geliştiren, eğlenceli deneylerle öğrenmeyi destekleyen atölye.',
                'capacity' => 18,
                'price' => 1000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Müzik ve Ritim',
                'description' => 'Müzik sevgisini aşılayan, ritim duygusunu geliştiren ve enstrüman çalmayı öğreten atölye.',
                'capacity' => 12,
                'price' => 1500.00,
                'status' => 'active',
            ],
            [
                'name' => 'Spor ve Hareket',
                'description' => 'Fiziksel gelişimi destekleyen, takım çalışmasını öğreten ve sağlıklı yaşam alışkanlıkları kazandıran atölye.',
                'capacity' => 25,
                'price' => 600.00,
                'status' => 'active',
            ],
        ];

        foreach ($workshops as $workshop) {
            Workshop::create($workshop);
        }
    }
}
