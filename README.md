# Laravel Searcher

Simple model and multi-model search.

## Installation

```bash
composer makidizajnerica/laravel-searcher
```

### Model Preparation

Model should implement `MakiDizajnerica\Searcher\Contracts\Searchable` interface, next define `attributesForTags()` method inside of it.
This method should return attribute values that will be used as model tags.
After that add `MakiDizajnerica\Searcher\Searchable` trait.

```php
<?php

use Illuminate\Database\Eloquent\Model;
use MakiDizajnerica\Searcher\Searchable;
use MakiDizajnerica\Searcher\Contracts\Searchable as SearchableContract;

class Post extends Model implements SearchableContract
{
    use Searchable;

    /**
     * Get model attributes that will be used for tags.
     * 
     * @return array<int, string>
     */
    public function attributesForTags(): array
    {
        return $this->only(['title']);
    }
}
```

Tags will be automaticly created on model creation, updated on model updation, and deleted when model is deleted.

If you want to create model without tags you should use `createWithoutTags()` method:

```php
<?php

use App\Models\Post;

$post = Post::createWithoutTags(['title' => 'Test']);
```

You can also update model without touching its tags:

```php
<?php

use App\Models\Post;

$post = Post::first();

// Example 1
$post->updateWithoutTags(['title' => 'New Test']);

// Example 2
$post->title = 'New Test';
$post->saveWithoutTags();
```

When deleting model, you can also disable tags deletion:

```php
<?php

use App\Models\Post;

$post = Post::first();

$post->withoutTags()->delete();
```

Models by default will be grouped by their table name, if you want to change that you can define `$searchType` property on the model:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use MakiDizajnerica\Searcher\Searchable;
use MakiDizajnerica\Searcher\Contracts\Searchable as SearchableContract;

class Post extends Model implements SearchableContract
{
    use Searchable;

    /**
     * @var string
     */
    protected $searchType = 'news';
}
```

## Usage

### Single Model Search

When searching single model, you can just pass query string to `whereTags()` scope.

```php
<?php

use App\Models\Post;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return view('post.index', [
            'posts' => Post::whereTags($request->query('search'))->paginate()
        ]);
    }
}
```

### Multiple Model Search

```php
<?php

use App\Models\User;
use App\Models\Post;
use MakiDizajnerica\Searcher\Search;
use Illuminate\Database\Eloquent\Builder;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return view('post.index', [
            'search' => (new Search)
                ->addModel(User::class, function (Builder $query) {
                    $query->active()->notAdministrator();
                })
                ->addModel(Post::class, ['latest', 'limit' => 10])
                ->search($request->query('search'))
        ]);
    }
}
```

Method's `addModel()` first parametar is class name of the model that will be searched. The second parametar is the scope, you can pass `Closure` and `array` like the example above.
You can also pass `string` which will represent the scope method name:

```php
<?php

use App\Models\Post;
use MakiDizajnerica\Searcher\Search;

(new Search)->addModel(Post::class, 'latest')->search('test', true);
```

You may also pass second `bool` parametar to the `search()` method, if you want strict searcing. By default its set to false, which means that tags will be search in LIKE clause.

When strict search is set to true, only the models with exact tags will be found.

Return value of the `search()` method will be `MakiDizajnerica\Searcher\Collections\SearchResultCollection` instance.

When rendering the search results, you can do something like this:

```
@foreach($search as $type => $results)
    <p>
        {{ $type }}
    </p>

    @foreach($results as $result)
        <div>
            <h1>
                {{ $result->title }}
            </h1>
        </div>
    @endforeach
@endforeach
```

Property `$results` will represent array of models that you can loop through.

## Author

**Nemanja Marijanovic** (<n.marijanovic@hotmail.com>) 

## Licence

Copyright © 2021, Nemanja Marijanovic <n.marijanovic@hotmail.com>

All rights reserved.

For the full copyright and license information, please view the LICENSE 
file that was distributed within the source root of this package.