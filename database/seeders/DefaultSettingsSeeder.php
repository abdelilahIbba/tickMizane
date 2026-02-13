<?php

namespace Database\Seeders;

use App\Services\SettingService;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settingService = app(SettingService::class);
        $settingService->initializeDefaults();
        
        $this->command->info('Default settings initialized successfully.');
    }
}
