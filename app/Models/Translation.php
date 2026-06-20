<?php

namespace App\Models;

use Database\Factories\TranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property string $locale
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @method \Illuminate\Database\Eloquent\Builder<static> byLocale(string $locale)
 * @method \Illuminate\Database\Eloquent\Builder<static> byKey(string $key)
 * @method \Illuminate\Database\Eloquent\Builder<static> searchContent(string $term)
 */
#[Fillable(['key', 'locale', 'content'])]
class Translation extends Model
{
    /** @use HasFactory<TranslationFactory> */
    use HasFactory;

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'translation_tag');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<self> $query */
    public function scopeByLocale($query, string $locale): void
    {
        $query->where('locale', $locale);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<self> $query */
    public function scopeByKey($query, string $key): void
    {
        $query->where('key', 'like', "{$key}%");
    }

    /** @param \Illuminate\Database\Eloquent\Builder<self> $query */
    public function scopeSearchContent($query, string $term): void
    {
        $query->where('content', 'like', "%{$term}%");
    }
}
