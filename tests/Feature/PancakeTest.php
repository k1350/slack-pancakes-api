<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\TodayPancakes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PancakeTest extends TestCase
{

    public function testIndex() {
        $response = $this->get('/api/pancake');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }
    
}