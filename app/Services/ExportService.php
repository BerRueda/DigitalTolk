<?php

namespace App\Services;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ExportService
{
    public const CACHE_KEY = 'translations.export';

    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepo,
    ) {}

    /** @return array<string, array<string, array{content: string, tags: string[], updated_at: string|null}>> */
    public function getExport(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->generate());
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, array<string, array{content: string, tags: string[], updated_at: string|null}>> */
    private function generate(): array
    {
        $result = [];

        foreach ($this->translationRepo->cursorForExport() as $translation) {
            $locale = $translation->locale;

            if (! isset($result[$locale])) {
                $result[$locale] = [];
            }

            $result[$locale][$translation->key] = [
                'content' => $translation->content,
                'tags' => $translation->tags->pluck('name')->toArray(),
                'updated_at' => $translation->updated_at?->toISOString(),
            ];
        }

        return $result;
    }
}
