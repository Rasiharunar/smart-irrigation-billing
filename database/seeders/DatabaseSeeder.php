<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pump;
use App\Models\Tariff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@smartirrigation.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+62812345678',
            'address' => 'Smart Irrigation Office',
        ]);

        // Create a farmer user
        $farmer = User::create([
            'name' => 'Pak Tani',
            'email' => 'farmer@example.com',
            'password' => Hash::make('password'),
            'role' => 'farmer',
            'phone' => '+62812345679',
            'address' => 'Sawah Block A, Desa Sukamaju',
        ]);

        // Create default tariff
        Tariff::create([
            'name' => 'Standard Tariff',
            'rate_per_kwh' => 1500.00,
            'description' => 'Standard electricity rate for irrigation pumps',
            'is_active' => true,
            'effective_from' => now(),
        ]);

        // Create sample pumps
        Pump::create([
            'name' => 'Pump A1',
            'location' => 'Sawah Block A1',
            'description' => 'Main pump for rice field irrigation in Block A1',
            'is_active' => true,
            'relay_pin' => 2,
            'max_power_kwh' => 5.0,
            'created_by' => $admin->id,
        ]);

        Pump::create([
            'name' => 'Pump B2',
            'location' => 'Sawah Block B2',
            'description' => 'Secondary pump for rice field irrigation in Block B2',
            'is_active' => true,
            'relay_pin' => 3,
            'max_power_kwh' => 3.5,
            'created_by' => $admin->id,
        ]);

        Pump::create([
            'name' => 'Pump C3',
            'location' => 'Sawah Block C3',
            'description' => 'Backup pump for emergency irrigation',
            'is_active' => false,
            'relay_pin' => 4,
            'max_power_kwh' => 4.0,
            'created_by' => $admin->id,
        ]);
    }
}