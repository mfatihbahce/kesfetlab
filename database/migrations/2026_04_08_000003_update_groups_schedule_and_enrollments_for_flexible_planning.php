<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->json('day_of_weeks')->nullable()->after('day_of_week');
            $table->date('group_start_date')->nullable()->after('end_time');
            $table->date('group_end_date')->nullable()->after('group_start_date');
        });

        DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled','graduated') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE enrollments MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['day_of_weeks', 'group_start_date', 'group_end_date']);
        });
    }
};

