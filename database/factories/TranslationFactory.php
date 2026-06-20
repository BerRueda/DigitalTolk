<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    private const LOCALES = ['en', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ar'];

    public function definition(): array
    {
        return [
            'key' => fake()->word().'.'.fake()->word().'.'.fake()->word(),
            'locale' => fake()->randomElement(self::LOCALES),
            'content' => fake()->sentence(),
        ];
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn () => ['locale' => $locale]);
    }
}
