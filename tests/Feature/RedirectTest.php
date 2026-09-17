<?php

namespace Tests\Feature;

use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_code_redirects_to_original_url_and_increments_clicks(): void
    {
        $user = User::factory()->create();
        $url = Url::create([
            'user_id'      => $user->id,
            'original_url' => 'https://laravel.com/docs/12.x',
            'short_code'   => 'docs12',
            'click_count'  => 0,
        ]);

        $response = $this->get('/docs12');

        $response->assertRedirect('https://laravel.com/docs/12.x');

        $this->assertEquals(1, $url->fresh()->click_count);

        // Visit a second time
        $this->get('/docs12');
        $this->assertEquals(2, $url->fresh()->click_count);
    }

    public function test_nonexistent_short_code_returns_404(): void
    {
        $response = $this->get('/nonexistent-code-999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Short URL not found.',
            ]);
    }
}
