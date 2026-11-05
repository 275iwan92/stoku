<?php

namespace Database\Seeders;

use App\Support\Company;
use App\Support\Constants;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/* REMARK
 * Tujuan       : Identitas perusahaan contoh (PT XYZ + logo) supaya kop cetakan dan menu Perusahaan terisi dan mudah dipahami. Bisa diubah dari layar.
 * Output       : sys_constant company.*, storage/app/company/logo.png.
 * Dipakai oleh : DatabaseSeeder.
 * Status       : Aktif (data contoh).
 */
class CompanySeeder extends Seeder
{
    public const COMPANY = [
        'name' => 'PT XYZ', 'legal_name' => 'PT XYZ', 'address' => "Jl. Contoh Raya No. 1\nSurabaya, Jawa Timur",
        'phone' => '031-0000000', 'email' => 'info@xyz.example', 'npwp' => '00.000.000.0-000.000',
        'weather_city' => 'Surabaya', 'weather_lat' => '-7.2575', 'weather_lon' => '112.7521',
    ];

    public function run(): void
    {
        foreach (self::COMPANY as $key => $value) {
            Constants::set('company.'.$key, $value, 'company');
        }
        Constants::set('company.kop_style', 'logo_name_address', 'company');
        File::ensureDirectoryExists(Company::dir());
        File::copy(__DIR__.'/assets/logo-xyz.png', Company::dir().DIRECTORY_SEPARATOR.Company::LOGO_FILE);
        Constants::set('company.logo', Company::LOGO_FILE, 'company');
    }
}
