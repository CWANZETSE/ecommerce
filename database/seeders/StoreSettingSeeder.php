<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'store_name',              'value' => 'My Store',       'group' => 'general'],
            ['key' => 'store_email',             'value' => '',               'group' => 'contact'],
            ['key' => 'store_phone',             'value' => '',               'group' => 'contact'],
            ['key' => 'store_address',           'value' => '',               'group' => 'contact'],
            ['key' => 'store_logo',              'value' => null,             'group' => 'general'],
            ['key' => 'store_tagline',           'value' => '',               'group' => 'general'],
            ['key' => 'currency',                'value' => 'KES',            'group' => 'general'],
            ['key' => 'timezone',                'value' => 'Africa/Nairobi', 'group' => 'general'],
            ['key' => 'order_prefix',            'value' => 'ORD-',           'group' => 'general'],
            ['key' => 'tax_rate',                'value' => '0',              'group' => 'tax'],
            ['key' => 'tax_enabled',             'value' => 'false',          'group' => 'tax'],
            ['key' => 'free_shipping_enabled',   'value' => 'true',           'group' => 'shipping'],
            ['key' => 'free_shipping_threshold', 'value' => '5000',           'group' => 'shipping'],
            ['key' => 'featured_products_limit', 'value' => '8',              'group' => 'homepage'],
            ['key' => 'new_arrivals_limit',      'value' => '8',              'group' => 'homepage'],
            ['key' => 'low_stock_threshold',     'value' => '5',              'group' => 'system'],
            ['key' => 'maintenance_mode',        'value' => 'false',          'group' => 'system'],
        ];

        foreach ($defaults as $row) {
            StoreSetting::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}