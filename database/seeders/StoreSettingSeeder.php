<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'store_name',              'value' => 'Bazario',       'group' => 'general'],
            ['key' => 'store_email',             'value' => 'info@bazariotechnologies.co.ke',               'group' => 'contact'],
            ['key' => 'store_phone',             'value' => '+254184391395',               'group' => 'contact'],
            ['key' => 'store_address',           'value' => 'Muthithi Road, Westlands,Nairobi',               'group' => 'contact'],
            ['key' => 'store_logo',              'value' => null,             'group' => 'general'],
            ['key' => 'store_tagline',           'value' => 'Technology You Can Trust',               'group' => 'general'],
            ['key' => 'currency',                'value' => 'KES',            'group' => 'general'],
            ['key' => 'timezone',                'value' => 'Africa/Nairobi', 'group' => 'general'],
            ['key' => 'order_prefix',            'value' => 'ORD-',           'group' => 'general'],
            ['key' => 'tax_rate',                'value' => '0',              'group' => 'tax'],
            ['key' => 'tax_enabled',             'value' => 'false',          'group' => 'tax'],
            ['key' => 'featured_products_limit', 'value' => '8',              'group' => 'homepage'],
            ['key' => 'new_arrivals_limit',      'value' => '8',              'group' => 'homepage'],
            ['key' => 'maintenance_mode',        'value' => 'false',          'group' => 'system'],
            [
                'key' => 'notification_email_enabled',
                'value' => 'false',
                'group' => 'notifications',
            ],
            [
                'key' => 'notification_sms_enabled',
                'value' => 'true',
                'group' => 'notifications',
            ],
            [
                'key' => 'notification_whatsapp_enabled',
                'value' => 'false',
                'group' => 'notifications',
            ],
        ];

        foreach ($defaults as $row) {
            StoreSetting::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
