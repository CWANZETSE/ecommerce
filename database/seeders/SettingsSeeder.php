<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'store_name', 'value' => 'My Store', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'KES', 'group' => 'general'],
            ['key' => 'tax_rate', 'value' => '16.00', 'group' => 'tax'],
            ['key' => 'tax_enabled', 'value' => 'true', 'group' => 'tax'],
        ];

        foreach ($settings as $setting) {
            StoreSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}