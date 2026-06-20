<?php

namespace App\Contracts\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    public function findById(int $id): ?Tag;

    public function findByName(string $name): ?Tag;

    public function getAll(): Collection;

    public function create(array $data): Tag;

    public function update(Tag $tag, array $data): Tag;

    public function delete(Tag $tag): bool;

    public function findOrCreate(string $name): Tag;
}
