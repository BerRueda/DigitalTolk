<?php

namespace App\Contracts\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    public function findById(int $id): ?Tag;

    public function findByName(string $name): ?Tag;

    /** @return Collection<int, Tag> */
    public function getAll(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): Tag;

    /** @param array<string, mixed> $data */
    public function update(Tag $tag, array $data): Tag;

    public function delete(Tag $tag): bool;

    public function findOrCreate(string $name): Tag;
}
