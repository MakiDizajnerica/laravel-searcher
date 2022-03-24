<?php

namespace MakiDizajnerica\Searcher\Contracts;

interface Searchable
{
    /**
     * Get model attributes that will be used for tags.
     * 
     * @return array<int, string>
     */
    public function attributesForTags(): array;

    /**
     * Map model properties that will be available
     * inside SearchResultCollection.
     * 
     * @return array
     */
    public function mapForSearch(): array;
}
