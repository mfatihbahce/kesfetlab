<?php

namespace Database\Seeders\Patches;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class V1Seed extends Seeder
{
    /**
     * Seed application branding defaults (idempotent).
     */
    public function run(): void
    {
        AppSetting::setValue('brand_name', AppSetting::getValue('brand_name', 'Kesfet LAB'));
        AppSetting::setValue('brand_logo_path', AppSetting::getValue('brand_logo_path'));
    }
}
