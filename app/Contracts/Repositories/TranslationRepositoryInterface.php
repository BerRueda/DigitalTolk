<?php

namespace App\Contracts\Repositories;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

interface TranslationRepositoryInterface
{
    public function findById(int $id): ?Translation;

    public function findByKeyAndLocale(string $key, string $locale): ?Translation;

    /**
     * @param array<string, mixed> $filters
     * @param array<int, string> $with
     * @return LengthAwarePaginator<int, Translation>
     */
    public function getAll(
        array $filters = [],
        int $perPage = 50,
        array $with = [],
    ): LengthAwarePaginator;

    /** @param array<string, mixed> $data */
    public function create(array $data): Translation;

    /** @param array<string, mixed> $data */
    public function update(Translation $translation, array $data): Translation;

    public function delete(Translation $translation): bool;

    /** @return LazyCollection<int, Translation> */
    public function cursorForExport(): LazyCollection;
}
