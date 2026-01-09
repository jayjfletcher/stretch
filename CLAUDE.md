# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build & Test Commands

```bash
composer test              # Run all tests with Pest
composer test -- --filter="test name"  # Run a single test
composer analyse           # Run PHPStan static analysis
composer format            # Format code with Pint
```

## Architecture

Stretch is a Laravel package providing a fluent query builder for Elasticsearch. The package follows Laravel conventions with service provider registration and facade access.

### Core Components

- **`Stretch`** (`src/Stretch.php`) - Main entry point, provides index management and document operations. Accessed via `Stretch` facade.
- **`ElasticsearchQueryBuilder`** (`src/Builders/ElasticsearchQueryBuilder.php`) - Fluent query builder implementing `QueryBuilderContract`. Created via `Stretch::index()` or `Stretch::query()`.
- **`MultiQueryBuilder`** (`src/Builders/MultiQueryBuilder.php`) - Executes multiple named queries in a single request via `Stretch::multi()`. Queries are added with `->add('name', callback)` and executed together.
- **`BoolQueryBuilder`** (`src/Builders/BoolQueryBuilder.php`) - Handles bool queries with must/should/filter/mustNot clauses.
- **`AggregationBuilder`** (`src/Builders/AggregationBuilder.php`) - Builds aggregations (terms, date histogram, metrics) with sub-aggregation support.
- **`RangeQueryBuilder`** (`src/Builders/RangeQueryBuilder.php`) - Chainable range query methods (gt/gte/lt/lte).
- **`ElasticPaginator`** (`src/Pagination/ElasticPaginator.php`) - Extends Laravel's `LengthAwarePaginator` for Elasticsearch results. Use `ElasticPaginator::fromResults()` to create from query results.

### Service Registration

`StretchServiceProvider` registers:
- `elasticsearch.manager` - Multi-connection manager singleton
- `ClientContract` - Wraps native Elasticsearch client
- `stretch` - Main Stretch singleton with client and manager

### Query Builder Pattern

The query builder uses a builder pattern with internal arrays (`$query`, `$aggregations`, `$sort`, etc.) that are assembled in `build()` and sent via `execute()`. Multiple queries added without explicit bool wrapping are auto-wrapped in `bool.must`.

### Multi-Connection Support

Multiple Elasticsearch connections can be configured in `config/stretch.php` under `connections`. Switch connections via `Stretch::connection('name')` or `$queryBuilder->connection('name')`.

### Caching

Query result caching is provided via the `IsCacheable` trait (`src/Builders/Concerns/IsCacheable.php`), used by both `ElasticsearchQueryBuilder` and `MultiQueryBuilder`.

- Enable caching: `->cache()` or `->setCacheEnabled(true)`
- Set TTL (stale-while-revalidate): `->setCacheTtl([300, 600])` (fresh for 5 min, stale for 10 min)
- Custom cache store: `->setCacheStore('redis')`
- Custom prefix: `->setCachePrefix('myapp:')`
- Clear cache before execution: `->clearCache()`

Configuration defaults are in `config/stretch.php` under the `cache` key.

### Configuration

The `config/stretch.php` file includes:
- `connections` - Multi-connection Elasticsearch settings
- `query` - Default size, max size, timeout
- `aggregations` - Max buckets, default size
- `logging` - Query logging, slow query threshold
- `cache` - TTL, prefix, store settings
