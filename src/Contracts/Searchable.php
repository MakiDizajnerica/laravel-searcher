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
}
