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
            $table->text('fcm_token')->nullable()->after('api_token');
            $table->string('device_type', 20)->nullable()->after('fcm_token');
            $table->timestamp('fcm_token_updated_at')->nullable()->after('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_users', function (Blueprint $table) {
            $table->dropColumn(['fcm_token', 'device_type', 'fcm_token_updated_at']);
        });
    }
};
