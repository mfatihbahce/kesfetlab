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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Grup adı
            $table->foreignId('workshop_id')->constrained()->onDelete('cascade'); // Sınıf ilişkisi
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade'); // Eğitmen
            $table->integer('capacity')->default(20); // Kontenjan
            $table->integer('current_enrollment')->default(0); // Mevcut kayıt sayısı
            
            // Ders Programı
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            
            // Grup Durumu
            $table->enum('status', ['active', 'inactive', 'full'])->default('active');
            $table->text('description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
