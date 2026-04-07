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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // Öğrenci Bilgileri
            $table->string('first_name');
            $table->string('last_name');
            $table->string('tc_identity', 11)->unique(); // T.C. Kimlik No
            $table->date('birth_date');
            $table->text('address');
            $table->text('health_condition')->nullable(); // Sağlık durumu
            
            // Veli Bilgileri
            $table->string('parent_first_name');
            $table->string('parent_last_name');
            $table->string('parent_phone');
            $table->string('parent_email')->nullable();
            $table->string('parent_profession')->nullable();
            
            // Acil Durum Kişisi
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relation')->nullable(); // İlişki (Anne, Baba, vs.)
            
            // Kayıt Durumu
            $table->enum('registration_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable(); // Yönetici notları
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
