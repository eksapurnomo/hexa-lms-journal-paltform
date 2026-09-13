<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class IsolationTest extends TestCase
{
    use RefreshDatabase;
    public function test_isolation_proof()
    {
        $this->assertEquals('readylms_testing', DB::connection()->getDatabaseName());
        $this->assertTrue(true);
    }
}
