<?php

namespace App\Repositories;

use App\Contracts\Repositories\TagRepositoryInterface;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagRepository implements TagRepositoryInterface
{
    public function findById(int $id): ?Tag
    {
        return Tag::find($id);
    }

    public function findByName(string $name): ?Tag
    {
        return Tag::where('name', $name)->first();
    }

    /** @return Collection<int, Tag> */
    public function getAll(): Collection
    {
        return Tag::orderBy('name')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        return $tag;
    }

    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }

    public function findOrCreate(string $name): Tag
    {
        return Tag::firstOrCreate(['name' => $name]);
    }
}
