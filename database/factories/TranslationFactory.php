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

    private static int $keyCounter = 0;

    public function definition(): array
    {
        self::$keyCounter++;

        return [
            'key' => 'test.'.self::$keyCounter.'.key',
            'locale' => fake()->randomElement(self::LOCALES),
            'content' => fake()->sentence(),
        ];
    }

    public function forLocale(string $locale): static
    {
        return $this->state(fn () => ['locale' => $locale]);
    }

    public function withKey(string $key): static
    {
        return $this->state(fn () => ['key' => $key]);
    }
}
