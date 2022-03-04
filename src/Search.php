<?php

namespace MakiDizajnerica\Searcher;

use Closure;
use InvalidArgumentException;
use MakiDizajnerica\Searcher\Collections\SearchResultCollection;
use MakiDizajnerica\Searcher\Contracts\Searchable as SearchableContract;

class Search
{
    /**
     * @var array<int, \Illuminate\Database\Eloquent\Builder>
     */
    protected $models = [];

    /**
     * Add model for multiple search.
     * 
     * @param  string $model
     * @param  mixed $scope
     * @return \MakiDizajnerica\Searcher\Search
     * 
     * @throws \InvalidArgumentException
     */
    public function addModel($model, $scope = null)
    {
        if (! in_array(SearchableContract::class, class_implements($model) ?? [])) {
            throw new InvalidArgumentException(
                "Class '{$model}' must implement 'MakiDizajnerica\Searcher\Contracts\Searchable' interface."
            );
        }

        $this->models[] = $this->formatQuery($model::query(), $scope);

        return $this;
    }

    /**
     * Search multiple models.
     * 
     * @param  string $search
     * @param  bool $strict
     * @return \MakiDizajnerica\Searcher\Collections\SearchResultCollection
     */
    public function search(string $search = '', $strict = false): SearchResultCollection
    {
        $collection = new SearchResultCollection;

        if (! blank($search)) {
            foreach ($this->models as $model) {
                $collection->addResults($model->whereTags($search, $strict)->get());
            }
        }

        return $collection->groupBySearchType();
    }

    /**
     * Apply scopes to query builder.
     * 
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $scope
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function formatQuery($query, $scope)
    {
        switch (true) {
            case $scope instanceof Closure:
                $scope($query);
                break;
            case is_array($scope):
                foreach ($scope as $method => $value) {
                    if (is_string($method)) {
                        $query->{$method}($value);

                        continue;
                    }

                    $query->{$value}();
                }
                break;
            case is_string($scope):
                $query->{$scope}();
                break;
        }

        return $query;
    }
}
