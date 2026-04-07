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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('workshop_id')->constrained()->onDelete('cascade');
            
            // Kayıt Durumu
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->date('enrollment_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            // Ödeme Bilgileri
            $table->decimal('amount', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->text('payment_notes')->nullable();
            
            // Özel Durumlar
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Aynı öğrenci aynı gruba birden fazla kayıt olamaz (group_id null olabilir)
            $table->unique(['student_id', 'group_id'], 'unique_student_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
