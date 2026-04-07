<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Workshop;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mevcut atölyeleri al
        $workshops = Workshop::all();
        
        if ($workshops->isEmpty()) {
            $this->command->info('Atölye bulunamadı. Önce WorkshopSeeder çalıştırın.');
            return;
        }

        // Enrollment'ı olmayan öğrencileri bul
        $studentsWithoutEnrollment = Student::whereDoesntHave('enrollments')->get();
        
        $this->command->info("Enrollment'ı olmayan {$studentsWithoutEnrollment->count()} öğrenci bulundu.");

        foreach ($studentsWithoutEnrollment as $index => $student) {
            // Atölye seç (sırayla)
            $workshop = $workshops[$index % $workshops->count()];
            
            // Enrollment oluştur
            Enrollment::create([
                'student_id' => $student->id,
                'workshop_id' => $workshop->id,
                'group_id' => null, // Henüz grup atanmamış
                'status' => 'pending',
                'enrollment_date' => $student->created_at,
                'start_date' => $student->created_at,
                'amount' => $workshop->price,
                'payment_status' => 'pending',
                'is_active' => true,
            ]);
            
            $this->command->info("{$student->full_name} için {$workshop->name} atölyesi enrollment'ı oluşturuldu.");
        }
        
        $this->command->info('Enrollment seeder tamamlandı.');
    }
}
