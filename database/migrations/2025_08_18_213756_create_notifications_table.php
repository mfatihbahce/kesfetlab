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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Bildirim Türü ve Hedef
            $table->enum('type', ['lesson_cancelled', 'lesson_postponed', 'attendance_update', 'announcement', 'payment_reminder']);
            $table->enum('target_type', ['student', 'parent', 'instructor', 'group', 'all']); // Hedef kitle
            $table->unsignedBigInteger('target_id')->nullable(); // Hedef ID (öğrenci, grup vs.)
            
            // İçerik
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Ek veriler (JSON formatında)
            
            // Gönderim Bilgileri
            $table->string('sender_type')->default('system'); // system, instructor, admin
            $table->unsignedBigInteger('sender_id')->nullable();
            
            // Durum
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            
            // Bildirim Kanalları
            $table->boolean('send_sms')->default(false);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_push')->default(true);
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['target_type', 'target_id']);
            $table->index(['status', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
