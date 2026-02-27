<?php

namespace MakiDizajnerica\Searcher;

use MakiDizajnerica\Searcher\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Searchable
{
    /**
     * @var bool
     */
    private bool $withTags = true;

    /**
     * The tags that are assigned to the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Boot trait events.
     * 
     * @return void
     */
    public static function bootSearchable(): void
    {
        static::created(function ($model) {
            if ($model->withTags()) {
                $model->createTags();
            }
        });

        static::updated(function ($model) {
            if ($model->withTags()) {
                $model->updateTags();
            }
        });

        static::deleting(function ($model) {
            if ($model->withTags()) {
                $model->deleteTags();
            }
        });
    }

    /**
     * Get model search type for grouping.
     * 
     * @return string
     */
    public function getSearchType(): string
    {
        if (property_exists($this, 'searchType')) {
            return $this->searchType;
        }

        return $this->getTable();
    }

    /**
     * Save a new model without search tags and return the instance.
     *
     * @param  array $attributes
     * @return static
     */
    public static function createWithoutTags(array $attributes = []): static
    {
        $model = new static($attributes);

        $model->saveWithoutTags();

        return $model;
    }

    /**
     * Update the model in the database without search tags.
     *
     * @param  array $attributes
     * @param  array $options
     * @return bool
     */
    public function updateWithoutTags(array $attributes = [], array $options = []): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->saveWithoutTags($options);
    }

    /**
     * Save the model to the database without search tags.
     * 
     * @param  array $options
     * @return bool
     */
    public function saveWithoutTags(array $options = []): bool
    {
        return $this->withoutTags()->save($options);
    }

    /**
     * Do not touch tags for the next action.
     * 
     * @return $this
     */
    public function withoutTags(): static
    {
        $this->withTags = false;

        return $this;
    }

    /**
     * Check if model search tags should be touched.
     * 
     * @return bool
     */
    public function withTags(): bool
    {
        return $this->withTags;
    }

    /**
     * Filter through tags and remove blank values.
     * 
     * @param  array $tags
     * @return array
     */
    private function filterTags(array $tags): array
    {
        return array_filter(
            array_values($tags), fn ($tag) => ! blank($tag) && is_scalar($tag)
        );
    }

    /**
     * Get ids of the model tags.
     * 
     * @return array
     */
    private function getTagsIds(): array
    {
        $tags = Tag::getOrCreate(array_unique(
            $this->filterTags($this->attributesForTags())
        ));

        $tags->toQuery()->increment('used');

        return $tags->modelKeys();
    }



    public function createTags(): void
    {
        $this->tags()->attach($this->getTagsIds());
    }

    public function updateTags(): void
    {
        with($this->tags, function ($tags) {
            $tags->toQuery()->whereIn('used', [0, 1])->delete();
            $tags->toQuery()->where('used', '>', 1)->decrement('used');
        });

        $this->tags()->sync($this->getTagsIds());
    }

    public function deleteTags(): void
    {
        with($this->tags, function ($tags) {
            $tags->toQuery()->whereIn('used', [0, 1])->delete();
            $tags->toQuery()->where('used', '>', 1)->decrement('used');
        });

        $this->tags()->detach();
    }



    /**
     * Map model properties that will be available
     * inside SearchResultCollection.
     * 
     * @return array
     */
    public function mapForSearch(): array
    {
        return $this->attributes;
    }

    /**
     * Scope model with specific tags.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string|null $tags
     * @param  bool $strict
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereTags(Builder $query,
                                   string|null $tags = null,
                                   bool $strict = false): Builder
    {
        $tags = $this->parseTags($tags);

        return $query->when(! blank($tags), function ($query) use ($tags, $strict) {
            $query->where(function ($query) use ($tags, $strict) {
                $query->whereHas('tags', function ($query) use ($tags, $strict) {
                    $query->whereIn('tags.tag', $tags)->when(! $strict, function ($query) use ($tags) {
                        $query->orWhere(function ($query) use ($tags) {
                            foreach ($tags as $tag) {
                                $query->orWhere('tags.tag', 'LIKE', '%' . $tag . '%');
                            }
                        });
                    });
                });
            });
        });
    }

    /**
     * Convert provided tags to array.
     * 
     * @param  mixed $tags
     * @return array
     */
    private function parseTags(mixed $tags): array
    {
        if (blank($tags)) {
            return [];
        }

        if (is_string($tags)) {
            $tags = explode(' ', $tags);
        }

        if (is_array($tags)) {
            return $this->filterTags($tags);
        }

        return [];
    }
}
