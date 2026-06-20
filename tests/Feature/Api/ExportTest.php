<?php

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExportTest extends TestCase
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

    public function test_can_export_translations(): void
    {
        $tag = Tag::factory()->create(['name' => 'mobile']);
        $translation = Translation::factory()->create([
            'key' => 'auth.login.title',
            'locale' => 'en',
            'content' => 'Welcome',
        ]);
        $translation->tags()->attach($tag);

        Cache::forget(ExportService::CACHE_KEY);

        $response = $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $response->assertOk();
        $data = $response->json();

        $this->assertArrayHasKey('en', $data);
        $this->assertArrayHasKey('auth.login.title', $data['en']);
        $this->assertEquals('Welcome', $data['en']['auth.login.title']['content']);
        $this->assertEquals(['mobile'], $data['en']['auth.login.title']['tags']);
    }

    public function test_export_includes_all_locales(): void
    {
        Translation::factory()->create(['locale' => 'en']);
        Translation::factory()->create(['locale' => 'fr']);
        Translation::factory()->create(['locale' => 'es']);

        Cache::forget(ExportService::CACHE_KEY);

        $response = $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $response->assertOk();
        $data = $response->json();

        $this->assertArrayHasKey('en', $data);
        $this->assertArrayHasKey('fr', $data);
        $this->assertArrayHasKey('es', $data);
    }

    public function test_export_is_cached(): void
    {
        Translation::factory()->count(5)->create();

        Cache::forget(ExportService::CACHE_KEY);

        $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $this->assertTrue(Cache::has(ExportService::CACHE_KEY));
    }

    public function test_export_cache_is_busted_on_create(): void
    {
        Translation::factory()->count(3)->create();

        Cache::forget(ExportService::CACHE_KEY);

        $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $this->assertTrue(Cache::has(ExportService::CACHE_KEY));

        $this->withToken($this->token)
            ->postJson('/api/translations', [
                'key' => 'new.key',
                'locale' => 'en',
                'content' => 'New translation',
            ]);

        $this->assertFalse(Cache::has(ExportService::CACHE_KEY));
    }

    public function test_export_requires_authentication(): void
    {
        $response = $this->getJson('/api/export/translations');

        $response->assertUnauthorized();
    }
}
