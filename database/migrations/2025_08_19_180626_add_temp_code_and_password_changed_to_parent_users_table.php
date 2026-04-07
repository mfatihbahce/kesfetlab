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
        Schema::table('parent_users', function (Blueprint $table) {
            $table->string('temp_code', 6)->nullable()->after('password'); // 6 haneli geçici kod
            $table->boolean('password_changed')->default(false)->after('temp_code'); // İlk giriş kontrolü
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_users', function (Blueprint $table) {
            $table->dropColumn(['temp_code', 'password_changed']);
        });
    }
};
