<?php

namespace Tests\Unit\Services;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Translation;
use App\Services\ExportService;
use App\Services\TranslationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Cache;
use Mockery;

class TranslationServiceTest extends TestCase
{
    private TranslationRepositoryInterface $repo;

    private TranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var TranslationRepositoryInterface&Mockery\MockInterface $repo */
        $this->repo = Mockery::mock(TranslationRepositoryInterface::class);
        $this->service = new TranslationService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_find_delegates_to_repository(): void
    {
        $translation = Translation::factory()->make(['id' => 1]);

        $this->repo->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($translation);

        $result = $this->service->find(1);

        $this->assertSame($translation, $result);
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->repo->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->service->find(999);

        $this->assertNull($result);
    }

    public function test_list_delegates_to_repository(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->repo->shouldReceive('getAll')
            ->once()
            ->with(['locale' => 'en'], 20, ['tags'])
            ->andReturn($paginator);

        $result = $this->service->list(['locale' => 'en'], 20);

        $this->assertSame($paginator, $result);
    }

    public function test_create_busts_cache(): void
    {
        $data = ['key' => 'test.key', 'locale' => 'en', 'content' => 'Hello'];
        $translation = Translation::factory()->make(['id' => 1] + $data);

        $this->repo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($translation);

        Cache::shouldReceive('forget')
            ->once()
            ->with(ExportService::CACHE_KEY)
            ->andReturnTrue();

        $result = $this->service->create($data);

        $this->assertSame($translation, $result);
    }

    public function test_update_busts_cache(): void
    {
        $existing = Translation::factory()->make(['id' => 1, 'key' => 'test.key', 'locale' => 'en']);
        $data = ['content' => 'Updated'];

        $this->repo->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($existing);

        $this->repo->shouldReceive('update')
            ->once()
            ->with($existing, $data)
            ->andReturnUsing(fn ($t, $d) => $t);

        Cache::shouldReceive('forget')
            ->once()
            ->with(ExportService::CACHE_KEY)
            ->andReturnTrue();

        $result = $this->service->update(1, $data);

        $this->assertSame($existing, $result);
    }

    public function test_update_throws_when_not_found(): void
    {
        $this->repo->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Translation not found.');

        $this->service->update(999, ['content' => 'test']);
    }

    public function test_delete_busts_cache(): void
    {
        $translation = Translation::factory()->make(['id' => 1]);

        $this->repo->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($translation);

        $this->repo->shouldReceive('delete')
            ->once()
            ->with($translation)
            ->andReturnTrue();

        Cache::shouldReceive('forget')
            ->once()
            ->with(ExportService::CACHE_KEY)
            ->andReturnTrue();

        $this->service->delete(1);
    }

    public function test_delete_throws_when_not_found(): void
    {
        $this->repo->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Translation not found.');

        $this->service->delete(999);
    }
}
