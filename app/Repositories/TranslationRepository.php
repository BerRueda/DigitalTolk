<?php

namespace App\Repositories;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

class TranslationRepository implements TranslationRepositoryInterface
{
    public function findById(int $id): ?Translation
    {
        return Translation::with('tags')->find($id);
    }

    public function findByKeyAndLocale(string $key, string $locale): ?Translation
    {
        return Translation::with('tags')
            ->where('key', $key)
            ->where('locale', $locale)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int, string>  $with
     * @return LengthAwarePaginator<int, Translation>
     */
    public function getAll(
        array $filters = [],
        int $perPage = 50,
        array $with = [],
    ): LengthAwarePaginator {
        $query = Translation::query();

        if ($with !== []) {
            $query->with($with);
        }

        if ($locale = $filters['locale'] ?? null) {
            $query->where('locale', $locale);
        }

        if ($key = $filters['key'] ?? null) {
            $query->where('key', 'like', "{$key}%");
        }

        if ($content = $filters['content'] ?? null) {
            $query->where('content', 'like', "%{$content}%");
        }

        if ($tagIds = $filters['tag_ids'] ?? null) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', (array) $tagIds));
        }

        return $query->latest()->paginate($perPage);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Translation
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        if (isset($data['content'])) {
            $data['content'] = strip_tags($data['content']);
        }

        $translation = Translation::create($data);

        if ($tags !== []) {
            /** @var array<int, string> $tags */
            $tagIds = collect($tags)
                ->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id)
                ->toArray();
            $translation->tags()->attach($tagIds);
        }

        return $translation->load('tags');
    }

    /** @param array<string, mixed> $data */
    public function update(Translation $translation, array $data): Translation
    {
        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        if (isset($data['content'])) {
            $data['content'] = strip_tags($data['content']);
        }

        $translation->update($data);

        if ($tags !== null) {
            /** @var array<int, string> $tags */
            $tagIds = collect($tags)
                ->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id)
                ->toArray();
            $translation->tags()->sync($tagIds);
        }

        return $translation->load('tags');
    }

    public function delete(Translation $translation): bool
    {
        return $translation->delete();
    }

    /** @return LazyCollection<int, Translation> */
    public function cursorForExport(): LazyCollection
    {
        return Translation::select(['id', 'locale', 'key', 'content', 'updated_at'])
            ->with('tags')
            ->orderBy('id')
            ->cursor();
    }
}
