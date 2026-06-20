<?php

namespace App\Contracts\Repositories;

use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

interface TranslationRepositoryInterface
{
    public function findById(int $id): ?Translation;

    public function findByKeyAndLocale(string $key, string $locale): ?Translation;

    public function getAll(
        array $filters = [],
        int $perPage = 50,
        array $with = [],
    ): LengthAwarePaginator;

    public function create(array $data): Translation;

    public function update(Translation $translation, array $data): Translation;

    public function delete(Translation $translation): bool;

    public function cursorForExport(): LazyCollection;
}
