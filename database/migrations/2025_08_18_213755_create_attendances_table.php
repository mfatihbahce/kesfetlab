<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            
            // Ders Bilgileri
            $table->date('lesson_date');
            $table->time('lesson_start_time');
            $table->time('lesson_end_time');
            
            // Katılım Durumu
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('excuse_note')->nullable(); // Mazeret notu
            $table->date('excuse_submitted_date')->nullable(); // Mazeret bildirim tarihi
            
            // Telafi Dersi
            $table->date('makeup_lesson_date')->nullable();
            $table->time('makeup_lesson_time')->nullable();
            $table->boolean('makeup_lesson_attended')->default(false);
            
            // Yoklama Bilgileri
            $table->timestamp('attendance_taken_at')->nullable();
            $table->text('notes')->nullable(); // Eğitmen notları
            
            $table->timestamps();
            
            // Aynı öğrenci aynı gün aynı grup için birden fazla yoklama kaydı olamaz
            $table->unique(['student_id', 'group_id', 'lesson_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
