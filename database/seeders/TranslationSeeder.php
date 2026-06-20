<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    use WithoutModelEvents;

    private const CHUNK_SIZE = 500;

    private const LOCALES = ['en', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ar'];

    public function run(): void
    {
        $this->command->info('Generating 100,000 translations...');

        $tags = $this->createTags();
        $keys = $this->generateKeys(10_000);
        $contentPool = $this->generateContentPool(500);

        $this->insertTranslations($keys, $contentPool);

        $this->attachTags($tags);

        $this->command->info('Done. Created '.Translation::count().' translations.');
    }

    /** @return array<int, Tag> */
    private function createTags(): array
    {
        $tagNames = [
            'mobile', 'web', 'desktop', 'api', 'admin',
            'frontend', 'backend', 'email', 'push', 'sms',
            'urgent', 'deprecated', 'beta', 'stable', 'legacy',
            'onboarding', 'checkout', 'search', 'profile', 'notification',
        ];

        $tags = [];

        foreach ($tagNames as $name) {
            $tags[] = Tag::create(['name' => $name]);
        }

        $this->command->info('Created '.count($tags).' tags.');

        return $tags;
    }

    /** @return array<int, string> */
    private function generateKeys(int $count): array
    {
        $prefixes = ['app', 'auth', 'nav', 'page', 'form', 'table', 'modal', 'sidebar', 'footer', 'header'];
        $middle = ['common', 'user', 'admin', 'home', 'about', 'contact', 'help', 'faq', 'blog', 'shop'];
        $suffixes = ['title', 'description', 'heading', 'label', 'placeholder', 'hint', 'help', 'error', 'success', 'info'];

        $keys = [];
        $index = 1;

        foreach ($prefixes as $prefix) {
            foreach ($middle as $mid) {
                foreach ($suffixes as $suffix) {
                    for ($i = 1; $i <= 10; $i++) {
                        $keys[] = "{$prefix}.{$mid}.{$suffix}_{$i}";
                        $index++;

                        if ($index > $count) {
                            break 4;
                        }
                    }
                }
            }
        }

        return $keys;
    }

    /** @return array<int, string> */
    private function generateContentPool(int $size): array
    {
        $pool = [];

        for ($i = 0; $i < $size; $i++) {
            $pool[] = fake()->sentence(rand(3, 10));
        }

        return $pool;
    }

    /**
     * @param  array<int, string>  $keys
     * @param  array<int, string>  $contentPool
     */
    private function insertTranslations(array $keys, array $contentPool): void
    {
        $now = now();
        $totalInserted = 0;
        $batch = [];
        $localeCount = count(self::LOCALES);
        $keyCount = count($keys);

        foreach (self::LOCALES as $localeIndex => $locale) {
            foreach ($keys as $keyIndex => $key) {
                $batch[] = [
                    'key' => $key,
                    'locale' => $locale,
                    'content' => $contentPool[array_rand($contentPool)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= self::CHUNK_SIZE) {
                    DB::table('translations')->insert($batch);
                    $totalInserted += count($batch);
                    $batch = [];

                    $this->command->info(
                        "Inserted {$totalInserted} / ".($localeCount * $keyCount).' translations...',
                    );
                }
            }
        }

        if ($batch !== []) {
            DB::table('translations')->insert($batch);
            $totalInserted += count($batch);
        }
    }

    /** @param array<int, Tag> $tags */
    private function attachTags(array $tags): void
    {
        $tagIds = array_map(fn (Tag $tag) => $tag->id, $tags);
        $translationIds = Translation::pluck('id');
        $batch = [];
        $pivotInserted = 0;

        $this->command->info('Attaching tags to translations...');

        foreach ($translationIds as $translationId) {
            $randomTags = (array) array_rand($tagIds, rand(1, 3));

            foreach ($randomTags as $tagIndex) {
                $batch[] = [
                    'translation_id' => $translationId,
                    'tag_id' => $tagIds[$tagIndex],
                ];

                if (count($batch) >= self::CHUNK_SIZE) {
                    DB::table('translation_tag')->insert($batch);
                    $pivotInserted += count($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            DB::table('translation_tag')->insert($batch);
        }

        $this->command->info("Attached {$pivotInserted} tag associations.");
    }
}
