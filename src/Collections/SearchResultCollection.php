<?php

namespace MakiDizajnerica\Searcher\Collections;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SearchResultCollection extends Collection
{
    /**
     * Add search results to collection.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @return void
     */
    public function addResults(EloquentCollection $results): void
    {
        if ($results->isEmpty()) {
            return;
        }

        $this->push(...$results->all());
    }

    /**
     * Group results by their defined search type.
     *
     * @return $this
     */
    public function groupBySearchType(): static
    {
        return $this->groupBy(fn ($result) => $result->getSearchType());
    }
}
