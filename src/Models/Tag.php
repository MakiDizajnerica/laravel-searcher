<?php

namespace MakiDizajnerica\Searcher\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'id';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'tag',
        'used',
    ];

    /**
     * Get tags or create new ones.
     *
     * @param  array $tags
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getOrCreate(array $tags)
    {
        $tagsToAdd = array_diff(
            $tags, ($allTags = static::whereIn('tag', $tags)->get())->pluck('tag')->all()
        );

        if (! blank($tagsToAdd)) {
            static::insert(array_map(fn ($tag) => ['tag' => $tag], $tagsToAdd));

            return $allTags->merge(static::whereIn('tag', $tagsToAdd)->get());
        }

        return $allTags;
    }
}
