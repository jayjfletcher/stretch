# Stretch - Laravel Elasticsearch Query Builder

A fluent, intuitive Laravel package for building Elasticsearch queries with comprehensive support for all major query types, aggregations, and advanced features.

## Installation

Install the package via Composer:

```bash
composer require seclock/stretch
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="stretch-config"
```

## Configuration

Configure your Elasticsearch connection in your `.env` file:

```env
ELASTICSEARCH_HOST=localhost:9200
ELASTICSEARCH_USERNAME=your_username
ELASTICSEARCH_PASSWORD=your_password
```

## Basic Usage

### Simple Queries

```php
use JayI\Stretch\Facades\Stretch;

// Simple match query
$results = Stretch::index(['index_1', 'index_2'])
    ->match('title', 'Laravel')
    ->execute();

// Term query
$results = Stretch::index(['index_1', 'index_2'])
    ->term('status', 'published')
    ->size(10)
    ->execute();

// Range query
$results = Stretch::index(['index_1', 'index_2'])
    ->range('created_at')
        ->gte('2024-01-01')
        ->lte('2024-12-31')
    ->execute();
```

### Bool Queries

```php
// Complex bool query
$results = Stretch::index(['index_1', 'index_2'])
    ->bool(function ($bool) {
        $bool->must([
            fn($q) => $q->match('title', 'Laravel'),
            fn($q) => $q->term('category', 'tutorial')
        ]);
        $bool->filter(fn($q) => $q->range('published_at')->gte('2024-01-01'));
        $bool->should(fn($q) => $q->term('featured', true));
        $bool->minimumShouldMatch(1);
    })
    ->execute();
```

### Aggregations

```php
// Terms aggregation
$results = Stretch::index(['index_1', 'index_2'])
    ->match('content', 'elasticsearch')
    ->aggregation('categories', fn($agg) => 
        $agg->terms('category.keyword')->size(10)
    )
    ->execute();

// Date histogram with sub-aggregations
$results = Stretch::index(['index_1', 'index_2'])
    ->aggregation('monthly_stats', fn($agg) =>
        $agg->dateHistogram('created_at', 'month')
            ->subAggregation('avg_score', fn($sub) => $sub->avg('score'))
            ->subAggregation('doc_count', fn($sub) => $sub->count())
    )
    ->execute();
```

### Sorting and Pagination

```php
$results = Stretch::index(['index_1', 'index_2'])
    ->match('title', 'Laravel')
    ->sort('created_at', 'desc')
    ->sort('_score', 'desc')
    ->size(20)
    ->from(0)
    ->execute();
```

### Source Filtering

```php
$results = Stretch::index(['index_1', 'index_2'])
    ->match('title', 'Laravel')
    ->source(['title', 'content', 'created_at'])
    ->execute();
```

### Highlighting

```php
$results = Stretch::index(['index_1', 'index_2'])
    ->match('content', 'elasticsearch')
    ->highlight([
        'content' => new \stdClass()
    ], [
        'pre_tags' => ['<strong>'],
        'post_tags' => ['</strong>']
    ])
    ->execute();
```

## Advanced Features

### Multi-Query Support

```php
 // Execute multiple searches in a single request
  $results = Stretch::multi()
      ->add('posts', fn ($q) => $q->match('title', 'Laravel')->size(10))
      ->add('users', fn ($q) => $q->term('status', 'active'))
      ->add(['logs', 'events'], fn ($q) => $q->range('timestamp')->gte('2024-01-01'))
      ->execute();

  // Access individual responses
  $postsResults = $results['responses'][0];
  $usersResults = $results['responses'][1];

  // Or use a query builder instance directly
  $postQuery = Stretch::query()->match('title', 'Laravel');
  Stretch::multi()
      ->add('posts', $postQuery)
      ->execute();

```

### Nested Queries

```php
$results = Stretch::index(['index_1', 'index_2'])
    ->nested('comments', function ($nested) {
        $nested->bool(function ($bool) {
            $bool->must(fn($q) => $q->match('comments.message', 'great'));
            $bool->filter(fn($q) => $q->range('comments.created_at')->gte('2024-01-01'));
        });
    })
    ->execute();
```

### Wildcard and Fuzzy Queries

```php
// Wildcard query
$results = Stretch::index(['index_1', 'index_2'])
    ->wildcard('title', 'Larave*')
    ->execute();

// Fuzzy query
$results = Stretch::index(['index_1', 'index_2'])
    ->fuzzy('title', 'Laravl', ['fuzziness' => 'AUTO'])
    ->execute();
```

### Multiple Query Types

```php
$results = Stretch::index(['index_1', 'index_2'])
    ->bool(function ($bool) {
        $bool->must(fn($q) => $q->match('title', 'Laravel'));
        $bool->should([
            fn($q) => $q->term('featured', true),
            fn($q) => $q->range('views')->gte(1000),
            fn($q) => $q->exists('premium_content')
        ]);
        $bool->filter(fn($q) => $q->terms('tags', ['php', 'web-development']));
        $bool->mustNot(fn($q) => $q->term('status', 'draft'));
    })
    ->execute();
```

## Index Management

```php
// Check if index exists
$exists = Stretch::indexExists('posts');

// Create an index
Stretch::createIndex('posts', [
    'settings' => [
        'number_of_shards' => 1,
        'number_of_replicas' => 0
    ],
    'mappings' => [
        'properties' => [
            'title' => ['type' => 'text'],
            'content' => ['type' => 'text'],
            'created_at' => ['type' => 'date']
        ]
    ]
]);

// Delete an index
Stretch::deleteIndex('posts');

// Get cluster health
$health = Stretch::health();
```

## Document Operations

```php
// Index a document
$result = Stretch::indexDocument('posts', [
    'title' => 'My Laravel Post',
    'content' => 'This is a great post about Laravel',
    'created_at' => now()->toISOString()
]);

// Update a document
$result = Stretch::updateDocument('posts', '123', [
    'title' => 'Updated Laravel Post'
]);

// Delete a document
$result = Stretch::deleteDocument('posts', '123');

// Bulk operations
$operations = [
    ['index' => ['_index' => 'posts', '_id' => '1']],
    ['title' => 'First Post', 'content' => 'Content 1'],
    ['index' => ['_index' => 'posts', '_id' => '2']],
    ['title' => 'Second Post', 'content' => 'Content 2']
];

$result = Stretch::bulk($operations);
```

## Query Debugging

```php
// Get the raw query array
$query = Stretch::index(['index_1', 'index_2'])
    ->match('title', 'Laravel')
    ->bool(function ($bool) {
        $bool->filter(fn($q) => $q->term('status', 'published'));
    })
    ->toArray();

dd($query); // Inspect the generated Elasticsearch query
```

## Available Query Types

- **Match Queries**: `match()`, `matchPhrase()`
- **Term Queries**: `term()`, `terms()`, `exists()`, `wildcard()`, `fuzzy()`
- **Range Queries**: `range()` with `gt()`, `gte()`, `lt()`, `lte()`
- **Bool Queries**: `bool()` with `must()`, `should()`, `filter()`, `mustNot()`
- **Nested Queries**: `nested()`

## Available Aggregations

- **Bucket Aggregations**: `terms()`, `dateHistogram()`, `range()`, `histogram()`
- **Metric Aggregations**: `avg()`, `sum()`, `min()`, `max()`, `count()`, `cardinality()`
- **Sub-Aggregations**: `subAggregation()`

## Configuration Options

The package supports extensive configuration options:

- Connection settings (hosts, authentication, SSL)
- Query defaults (size, timeout)
- Aggregation settings
- Logging and debugging
- Caching configuration

Check the published config file for all available options.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please message instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.