<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Workshop;
use App\Models\User;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workshops = Workshop::all();
        $instructors = User::where('role', 'instructor')->get();

        if ($workshops->count() > 0 && $instructors->count() > 0) {
            $groups = [
                [
                    'name' => 'Robotik A Grubu',
                    'workshop_id' => $workshops->first()->id,
                    'instructor_id' => $instructors->first()->id,
                    'capacity' => 15,
                    'current_enrollment' => 0,
                    'day_of_week' => 'monday',
                    'start_time' => '14:00',
                    'end_time' => '16:00',
                    'status' => 'active',
                    'description' => 'Pazartesi öğleden sonra robotik grubu',
                ],
                [
                    'name' => 'Robotik B Grubu',
                    'workshop_id' => $workshops->first()->id,
                    'instructor_id' => $instructors->first()->id,
                    'capacity' => 15,
                    'current_enrollment' => 0,
                    'day_of_week' => 'wednesday',
                    'start_time' => '14:00',
                    'end_time' => '16:00',
                    'status' => 'active',
                    'description' => 'Çarşamba öğleden sonra robotik grubu',
                ],
                [
                    'name' => 'Sanat A Grubu',
                    'workshop_id' => $workshops->skip(1)->first()->id,
                    'instructor_id' => $instructors->skip(1)->first()->id,
                    'capacity' => 20,
                    'current_enrollment' => 0,
                    'day_of_week' => 'tuesday',
                    'start_time' => '15:00',
                    'end_time' => '17:00',
                    'status' => 'active',
                    'description' => 'Salı öğleden sonra sanat grubu',
                ],
                [
                    'name' => 'Bilim A Grubu',
                    'workshop_id' => $workshops->skip(2)->first()->id,
                    'instructor_id' => $instructors->skip(2)->first()->id,
                    'capacity' => 18,
                    'current_enrollment' => 0,
                    'day_of_week' => 'thursday',
                    'start_time' => '14:30',
                    'end_time' => '16:30',
                    'status' => 'active',
                    'description' => 'Perşembe öğleden sonra bilim grubu',
                ],
                [
                    'name' => 'Müzik A Grubu',
                    'workshop_id' => $workshops->skip(3)->first()->id,
                    'instructor_id' => $instructors->skip(3)->first()->id,
                    'capacity' => 12,
                    'current_enrollment' => 0,
                    'day_of_week' => 'friday',
                    'start_time' => '16:00',
                    'end_time' => '18:00',
                    'status' => 'active',
                    'description' => 'Cuma öğleden sonra müzik grubu',
                ],
                [
                    'name' => 'Spor A Grubu',
                    'workshop_id' => $workshops->skip(4)->first()->id,
                    'instructor_id' => $instructors->first()->id,
                    'capacity' => 25,
                    'current_enrollment' => 0,
                    'day_of_week' => 'saturday',
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                    'status' => 'active',
                    'description' => 'Cumartesi sabah spor grubu',
                ],
            ];

            foreach ($groups as $group) {
                Group::create($group);
            }
        }
    }
}
