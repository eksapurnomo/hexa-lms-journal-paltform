<?php

namespace App\Services\Initialization;

use App\Models\Setting;

class SettingInitializer implements InitializerInterface
{
    public function getName(): string
    {
        return 'Settings';
    }

    public function initialize(bool $dryRun = false): InitializationResult
    {
        $result = new InitializationResult();

        if (Setting::query()->exists()) {
            $result->record('skip', 'Settings row already exists');
            return $result;
        }

        if (!$dryRun) {
            Setting::query()->create([
                'footer_text' => '',
                'footer_contact_number' => '',
                'footer_support_mail' => '',
                'footer_description' => '',
                'currency_position' => 'left',
            ]);
        }

        $result->record('create', 'Canonical settings row');
        return $result;
    }
}
