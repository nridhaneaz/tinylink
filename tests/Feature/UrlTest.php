<?php

namespace Tests\Feature;

use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_shortened_url_with_auto_generated_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/urls', [
            'url' => 'https://laravel.com/docs',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'original_url', 'short_code', 'click_count'],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'original_url' => 'https://laravel.com/docs',
                    'click_count'  => 0,
                ],
            ]);

        $this->assertEquals(6, strlen($response->json('data.short_code')));

        $this->assertDatabaseHas('urls', [
            'user_id'      => $user->id,
            'original_url' => 'https://laravel.com/docs',
        ]);
    }

    public function test_user_can_create_shortened_url_with_custom_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/urls', [
            'url'         => 'https://laravel.com/docs',
            'custom_code' => 'custom-link',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'original_url' => 'https://laravel.com/docs',
                    'short_code'   => 'custom-link',
                    'click_count'  => 0,
                ],
            ]);

        $this->assertDatabaseHas('urls', [
            'user_id'    => $user->id,
            'short_code' => 'custom-link',
        ]);
    }

    public function test_custom_code_must_be_unique(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1, 'sanctum')->postJson('/api/urls', [
            'url'         => 'https://example.com',
            'custom_code' => 'taken-code',
        ]);

        $response = $this->actingAs($user2, 'sanctum')->postJson('/api/urls', [
            'url'         => 'https://another.com',
            'custom_code' => 'taken-code',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['custom_code']);
    }

    public function test_url_creation_fails_with_invalid_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/urls', [
            'url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    public function test_user_can_list_only_their_own_urls(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Url::create([
            'user_id'      => $user1->id,
            'original_url' => 'https://user1.com',
            'short_code'   => 'code11',
            'click_count'  => 0,
        ]);

        Url::create([
            'user_id'      => $user2->id,
            'original_url' => 'https://user2.com',
            'short_code'   => 'code22',
            'click_count'  => 0,
        ]);

        $response = $this->actingAs($user1, 'sanctum')->getJson('/api/urls');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['data'],
            ]);

        $urls = $response->json('data.data');
        $this->assertCount(1, $urls);
        $this->assertEquals('https://user1.com', $urls[0]['original_url']);
    }

    public function test_user_can_view_their_own_url_details(): void
    {
        $user = User::factory()->create();
        $url = Url::create([
            'user_id'      => $user->id,
            'original_url' => 'https://laravel.com',
            'short_code'   => 'lrvl12',
            'click_count'  => 5,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/urls/{$url->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id'           => $url->id,
                    'original_url' => 'https://laravel.com',
                    'short_code'   => 'lrvl12',
                    'click_count'  => 5,
                ],
            ]);
    }

    public function test_user_cannot_view_another_users_url(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $url = Url::create([
            'user_id'      => $user1->id,
            'original_url' => 'https://laravel.com',
            'short_code'   => 'lrvl12',
            'click_count'  => 0,
        ]);

        $response = $this->actingAs($user2, 'sanctum')->getJson("/api/urls/{$url->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_own_url(): void
    {
        $user = User::factory()->create();
        $url = Url::create([
            'user_id'      => $user->id,
            'original_url' => 'https://laravel.com',
            'short_code'   => 'lrvl12',
            'click_count'  => 0,
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/urls/{$url->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'URL deleted successfully.',
            ]);

        $this->assertDatabaseMissing('urls', [
            'id' => $url->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_url(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $url = Url::create([
            'user_id'      => $user1->id,
            'original_url' => 'https://laravel.com',
            'short_code'   => 'lrvl12',
            'click_count'  => 0,
        ]);

        $response = $this->actingAs($user2, 'sanctum')->deleteJson("/api/urls/{$url->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('urls', [
            'id' => $url->id,
        ]);
    }

    public function test_user_can_view_url_statistics(): void
    {
        $user = User::factory()->create();
        $url = Url::create([
            'user_id'      => $user->id,
            'original_url' => 'https://laravel.com',
            'short_code'   => 'lrvl12',
            'click_count'  => 42,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/urls/{$url->id}/stats");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'url'         => 'https://laravel.com',
                    'short_code'  => 'lrvl12',
                    'click_count' => 42,
                ],
            ]);
    }

    public function test_user_cannot_view_stats_of_another_users_url(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $url = Url::create([
            'user_id'      => $user1->id,
            'original_url' => 'https://laravel.com',
            'short_code'   => 'lrvl12',
            'click_count'  => 42,
        ]);

        $response = $this->actingAs($user2, 'sanctum')->getJson("/api/urls/{$url->id}/stats");

        $response->assertStatus(403);
    }
}
