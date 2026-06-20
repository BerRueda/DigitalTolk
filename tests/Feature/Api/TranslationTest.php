<?php

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_translations(): void
    {
        Translation::factory()->count(3)->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/translations');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_create_translation(): void
    {
        Tag::factory()->create(['name' => 'mobile']);

        $response = $this->withToken($this->token)
            ->postJson('/api/translations', [
                'key' => 'auth.login.title',
                'locale' => 'en',
                'content' => 'Welcome',
                'tags' => ['mobile'],
            ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'key', 'locale', 'content', 'tags']])
            ->assertJsonPath('data.key', 'auth.login.title')
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.content', 'Welcome');
    }

    public function test_can_show_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson("/api/translations/{$translation->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'key', 'locale', 'content']]);
    }

    public function test_can_update_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->withToken($this->token)
            ->putJson("/api/translations/{$translation->id}", [
                'content' => 'Updated content',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.content', 'Updated content');
    }

    public function test_can_delete_translation(): void
    {
        $translation = Translation::factory()->create();

        $response = $this->withToken($this->token)
            ->deleteJson("/api/translations/{$translation->id}");

        $response->assertNoContent();

        $this->assertModelMissing($translation);
    }

    public function test_can_search_by_key(): void
    {
        Translation::factory()->create(['key' => 'auth.login.title']);
        Translation::factory()->create(['key' => 'nav.home.title']);

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?key=auth');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_search_by_locale(): void
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'fr']);

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?locale=fr');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_search_by_content(): void
    {
        Translation::factory()->create(['content' => 'Hello world']);
        Translation::factory()->create(['content' => 'Goodbye world']);

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?content=Hello');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_search_by_tags(): void
    {
        $tag = Tag::factory()->create();
        $translation = Translation::factory()->create();
        $translation->tags()->attach($tag);

        Translation::factory()->create();

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?tag_ids[]='.$tag->id);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_validates_required_fields_on_create(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/translations', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['key', 'locale', 'content']);
    }

    public function test_validates_unique_key_locale_combo(): void
    {
        Translation::factory()->create(['key' => 'test.key', 'locale' => 'en']);

        $response = $this->withToken($this->token)
            ->postJson('/api/translations', [
                'key' => 'test.key',
                'locale' => 'en',
                'content' => 'Duplicate',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['key']);
    }

    public function test_allows_same_key_for_different_locale(): void
    {
        Translation::factory()->create(['key' => 'test.key', 'locale' => 'en']);

        $response = $this->withToken($this->token)
            ->postJson('/api/translations', [
                'key' => 'test.key',
                'locale' => 'fr',
                'content' => 'French version',
            ]);

        $response->assertCreated();
    }
}
