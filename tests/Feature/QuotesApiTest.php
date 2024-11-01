<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class QuotesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user for testing
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_it_returns_five_unique_quotes_successfully()
    {
        // Mock the external API calls
        Http::fake([
            'https://api.kanye.rest/' => Http::sequence()
                ->push(['quote' => 'Quote 1'])
                ->push(['quote' => 'Quote 2'])
                ->push(['quote' => 'Quote 3'])
                ->push(['quote' => 'Quote 4'])
                ->push(['quote' => 'Quote 5'])
        ]);

        $response = $this->getJson(route('get.quotes'));

        $response->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJson([
                'Quote 1',
                'Quote 2',
                'Quote 3',
                'Quote 4',
                'Quote 5'
            ]);

        // Verify that the API was called exactly 5 times
        Http::assertSentCount(5);
    }

    public function test_it_handles_duplicate_quotes()
    {
        // Mock API to return some duplicate quotes
        Http::fake([
            'https://api.kanye.rest/' => Http::sequence()
                ->push(['quote' => 'Quote 1'])
                ->push(['quote' => 'Quote 1']) // Duplicate
                ->push(['quote' => 'Quote 2'])
                ->push(['quote' => 'Quote 3'])
                ->push(['quote' => 'Quote 4'])
                ->push(['quote' => 'Quote 5'])
        ]);

        $response = $this->getJson(route('get.quotes'));

        $response->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJson([
                'Quote 1',
                'Quote 2',
                'Quote 3',
                'Quote 4',
                'Quote 5'
            ]);

        // Verify that the API was called 6 times (due to one duplicate)
        Http::assertSentCount(6);
    }
}
