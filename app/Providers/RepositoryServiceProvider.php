<?php

namespace App\Providers;

use App\Contracts\Repositories\TagRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Repositories\TagRepository;
use App\Repositories\TranslationRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TranslationRepositoryInterface::class, TranslationRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
