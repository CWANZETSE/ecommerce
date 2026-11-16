<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class CountySeeder extends Seeder
{
    public function run(): void
    {
        $counties = [
            'Mombasa', 'Kwale', 'Kilifi', 'Tana River', 'Lamu',
            'Taita Taveta', 'Garissa', 'Wajir', 'Mandera', 'Marsabit',
            'Isiolo', 'Meru', 'Tharaka-Nithi', 'Embu', 'Kitui',
            'Machakos', 'Makueni', 'Nyandarua', 'Nyeri', 'Kirinyaga',
            "Murang'a", 'Kiambu', 'Turkana', 'West Pokot', 'Samburu',
            'Trans Nzoia', 'Uasin Gishu', 'Elgeyo-Marakwet', 'Nandi', 'Baringo',
            'Laikipia', 'Nakuru', 'Narok', 'Kajiado', 'Kericho',
            'Bomet', 'Kakamega', 'Vihiga', 'Bungoma', 'Busia',
            'Siaya', 'Kisumu', 'Homa Bay', 'Migori', 'Kisii',
            'Nyamira', 'Nairobi City',
        ];

        foreach ($counties as $county) {
            ShippingZone::firstOrCreate(
                ['name' => $county],
                ['fee' => 0.00, 'is_active' => true]
            );
        }
    }
}