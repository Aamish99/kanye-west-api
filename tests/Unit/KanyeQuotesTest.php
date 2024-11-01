<?php

namespace Tests\Unit;

use App\Livewire\KanyeQuotes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\User;

class KanyeQuotesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_mount_component_and_fetch_quotes()
    {
        Http::fake([
            '*/api/quotes' => Http::response([
                'Quote 1',
                'Quote 2'
            ], 200)
        ]);

        Livewire::actingAs($this->user)
            ->test(KanyeQuotes::class)
            ->assertSet('quotes', [
                'Quote 1',
                'Quote 2'
            ])
            ->assertViewHas('quotes');
    }

    /** @test */
    public function it_handles_failed_api_response()
    {
        Http::fake([
            '*/api/quotes' => Http::response(null, 500)
        ]);

        Livewire::actingAs($this->user)
            ->test(KanyeQuotes::class)
            ->assertSet('quotes', ['Error fetching quotes.']);
    }

    /** @test */
    public function it_can_refresh_quotes()
    {
        Http::fake([
            '*/api/quotes' => Http::sequence()
                ->push(['Quote 1', 'Quote 2'], 200)
                ->push(['Quote 3', 'Quote 4'], 200)
        ]);

        Livewire::actingAs($this->user)
            ->test(KanyeQuotes::class)
            ->assertSet('quotes', ['Quote 1', 'Quote 2'])
            ->call('refreshQuotes')
            ->assertSet('quotes', ['Quote 3', 'Quote 4']);
    }

    /** @test */
    public function it_creates_new_token_when_session_token_is_missing()
    {
        Http::fake([
            '*/api/quotes' => Http::response([
                'Quote 1',
                'Quote 2'
            ], 200)
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(KanyeQuotes::class);

        $this->assertNotNull(session('api_token'));

        // Verify that the token is used in subsequent requests
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer ' . session('api_token'));
        });
    }

    /** @test */
    public function it_reuses_existing_session_token()
    {
        $existingToken = 'existing-test-token';
        session(['api_token' => $existingToken]);

        Http::fake([
            '*/api/quotes' => Http::response([
                'Quote 1',
                'Quote 2'
            ], 200)
        ]);

        Livewire::actingAs($this->user)
            ->test(KanyeQuotes::class);

        Http::assertSent(function ($request) use ($existingToken) {
            return $request->hasHeader('Authorization', 'Bearer ' . $existingToken);
        });
    }

    /** @test */
    public function it_deletes_old_tokens_when_creating_new_one()
    {
        Http::fake([
            '*/api/quotes' => Http::response([
                'Quote 1',
                'Quote 2'
            ], 200)
        ]);

        // Create some existing tokens
        $this->user->createToken('Old Token 1');
        $this->user->createToken('Old Token 2');

        $this->assertEquals(2, $this->user->tokens()->count());

        Livewire::actingAs($this->user)
            ->test(KanyeQuotes::class);

        // Should only have one token after component mount
        $this->assertEquals(1, $this->user->tokens()->count());
    }
}
