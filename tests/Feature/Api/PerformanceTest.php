<?php

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    private const MAX_RESPONSE_TIME_MS = 200;

    private const MAX_EXPORT_TIME_MS = 500;

    private const SEED_COUNT = 1000;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken(
            'perf-token',
            ['translations:read', 'translations:write', 'export:read'],
        )->plainTextToken;
    }

    public function test_list_translations_response_time(): void
    {
        Translation::factory()->count(self::SEED_COUNT)->create();

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?per_page=50');

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "List endpoint took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_list_filtered_by_locale_response_time(): void
    {
        Translation::factory()->count(self::SEED_COUNT)->create();
        Translation::factory()->count(50)->create(['locale' => 'fr']);

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?locale=fr');

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "List (filtered by locale) took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_list_filtered_by_key_prefix_response_time(): void
    {
        Translation::factory()->count(self::SEED_COUNT)->create();

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?key=auth');

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "List (filtered by key) took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_create_translation_response_time(): void
    {
        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->postJson('/api/translations', [
                'key' => 'performance.test.key',
                'locale' => 'en',
                'content' => 'Performance test content',
            ]);

        $duration = (microtime(true) - $start) * 1000;

        $response->assertCreated();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "Create endpoint took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_show_translation_response_time(): void
    {
        $translation = Translation::factory()->hasTags(2)->create();

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->getJson("/api/translations/{$translation->id}");

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "Show endpoint took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_update_translation_response_time(): void
    {
        $translation = Translation::factory()->create();

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->putJson("/api/translations/{$translation->id}", [
                'content' => 'Updated performance test content',
            ]);

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "Update endpoint took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_delete_translation_response_time(): void
    {
        $translation = Translation::factory()->create();

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/translations/{$translation->id}");

        $duration = (microtime(true) - $start) * 1000;

        $response->assertNoContent();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "Delete endpoint took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_export_uncached_response_time(): void
    {
        Translation::factory()->count(self::SEED_COUNT)->create();

        Cache::forget(ExportService::CACHE_KEY);

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_EXPORT_TIME_MS,
            $duration,
            "Uncached export took {$duration}ms, expected < {$this->maxExportTimeMs()}ms",
        );
    }

    public function test_export_cached_response_time(): void
    {
        Translation::factory()->count(self::SEED_COUNT)->create();

        Cache::forget(ExportService::CACHE_KEY);

        $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "Cached export took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_search_by_tag_response_time(): void
    {
        $tag = Tag::factory()->create(['name' => 'perf-test']);
        Translation::factory()->count(self::SEED_COUNT)->create()->each(
            fn (Translation $t) => $t->tags()->attach($tag),
        );

        $start = microtime(true);

        $response = $this->withToken($this->token)
            ->getJson('/api/translations?tag_ids[]='.$tag->id);

        $duration = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::MAX_RESPONSE_TIME_MS,
            $duration,
            "Search by tag took {$duration}ms, expected < {$this->maxResponseTimeMs()}ms",
        );
    }

    public function test_export_with_1000_translations_memory_usage(): void
    {
        Translation::factory()->count(self::SEED_COUNT)->create();

        Cache::forget(ExportService::CACHE_KEY);

        $usageBefore = memory_get_usage();

        $this->withToken($this->token)
            ->getJson('/api/export/translations');

        $usageAfter = memory_get_usage();
        $peakUsage = memory_get_peak_usage(true);

        $memoryUsed = $peakUsage - $usageBefore;

        $this->assertLessThan(
            50 * 1024 * 1024,
            $memoryUsed,
            "Export used {$memoryUsed} bytes of memory, expected < 50MB",
        );
    }

    private function maxResponseTimeMs(): int
    {
        return self::MAX_RESPONSE_TIME_MS;
    }

    private function maxExportTimeMs(): int
    {
        return self::MAX_EXPORT_TIME_MS;
    }
}
