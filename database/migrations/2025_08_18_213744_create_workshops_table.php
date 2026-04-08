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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Sınıf adı
            $table->text('description')->nullable(); // Açıklama
            $table->integer('capacity')->default(20); // Kontenjan
            $table->decimal('price', 10, 2)->default(0); // Fiyat
            $table->enum('status', ['active', 'inactive'])->default('active'); // Durum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
