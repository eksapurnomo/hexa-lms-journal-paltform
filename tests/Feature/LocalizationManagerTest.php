<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LocalizationManagerTest extends TestCase
{
    public function test_valid_locale_is_accepted()
    {
        $this->withSession(['locale' => 'id'])
             ->get('/')
             ->assertStatus(200);

        $this->assertEquals('id', app()->getLocale());
    }

    public function test_invalid_locale_falls_back_to_default()
    {
        Config::set('app.fallback_locale', 'en');

        $this->withSession(['locale' => 'language=fr'])
             ->get('/');

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('en', session('locale'));
    }

    public function test_unsupported_locale_falls_back_to_default()
    {
        Config::set('app.fallback_locale', 'en');

        $this->withSession(['locale' => 'fr'])
             ->get('/');

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('en', session('locale'));
    }
}
