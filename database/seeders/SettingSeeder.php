<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $setting = Setting::latest('id')->first();

        SettingRepository::query()->updateOrCreate([
            'id' => $setting?->id ?? null
        ], [
            'footer_text' => $setting?->footer_text ?? 'Developed by Razinsoft',
            'footer_contact_number' => $setting?->footer_contact_number ?? '+8801963953998',
            'footer_support_mail' => $setting?->footer_support_mail ?? 'support@razinsoft.com',
            'footer_description' => $setting?->footer_description ?? 'Build skills with courses, certificates and get online knowledge from our world-class platform.',
            'currency_position' => $setting?->currency_position ?? 'Left',
            'play_store_url' => $setting?->play_store_url ?? null,
            'app_store_url' => $setting?->app_store_url ?? null,
        ]);
    }
}
