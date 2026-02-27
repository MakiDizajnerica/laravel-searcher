<?php

namespace MakiDizajnerica\Searcher\Collections;

use Illuminate\Database\Eloquent\Collection;

class SearchResultCollection extends Collection
{
    /**
     * Add search results to collection.
     * 
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @return void
     */
    public function addResults(Collection $results): void
    {
        $this->items = $this->merge(
            $results->pipe(fn ($results) => $results->all())
        );
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
