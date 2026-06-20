<?php

namespace App\Services;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepo,
    ) {}

    public function find(int $id): ?Translation
    {
        return $this->translationRepo->findById($id);
    }

    public function list(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return $this->translationRepo->getAll($filters, $perPage, ['tags']);
    }

    public function create(array $data): Translation
    {
        $translation = $this->translationRepo->create($data);

        Cache::forget(ExportService::CACHE_KEY);

        Log::info('Translation created', [
            'id' => $translation->id,
            'key' => $translation->key,
            'locale' => $translation->locale,
        ]);

        return $translation;
    }

    public function update(int $id, array $data): Translation
    {
        $translation = $this->translationRepo->findById($id);

        if ($translation === null) {
            Log::warning('Translation not found for update', ['id' => $id]);

            throw new \RuntimeException('Translation not found.');
        }

        $translation = $this->translationRepo->update($translation, $data);

        Cache::forget(ExportService::CACHE_KEY);

        Log::info('Translation updated', [
            'id' => $translation->id,
            'key' => $translation->key,
            'locale' => $translation->locale,
            'changes' => $data,
        ]);

        return $translation;
    }

    public function delete(int $id): void
    {
        $translation = $this->translationRepo->findById($id);

        if ($translation === null) {
            Log::warning('Translation not found for delete', ['id' => $id]);

            throw new \RuntimeException('Translation not found.');
        }

        $this->translationRepo->delete($translation);

        Cache::forget(ExportService::CACHE_KEY);

        Log::info('Translation deleted', [
            'id' => $id,
            'key' => $translation->key,
            'locale' => $translation->locale,
        ]);
    }
}
