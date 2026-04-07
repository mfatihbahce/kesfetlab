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
        Schema::create('parent_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_user_id')->constrained('parent_users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('relationship', ['parent', 'guardian', 'other'])->default('parent');
            $table->boolean('is_primary')->default(false); // Ana veli mi?
            $table->timestamps();
            
            // Bir öğrencinin aynı veliye birden fazla kez atanmaması için
            $table->unique(['parent_user_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_students');
    }
};
