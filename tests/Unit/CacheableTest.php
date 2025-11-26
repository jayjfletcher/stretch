<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use JayI\Stretch\Builders\ElasticsearchQueryBuilder;
use JayI\Stretch\Builders\MultiQueryBuilder;
use JayI\Stretch\Contracts\ClientContract;

beforeEach(function () {
    config(['stretch.cache.ttl' => [300, 600]]);
    config(['stretch.cache.prefix' => '']);
    config(['stretch.cache.store' => 'default']);
});

it('can enable caching with cache method', function () {
    $builder = new ElasticsearchQueryBuilder;

    expect($builder->isCacheEnabled())->toBeFalse();

    $builder->cache();

    expect($builder->isCacheEnabled())->toBeTrue();
});

it('can enable caching with setCacheEnabled', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->setCacheEnabled(true);

    expect($builder->getCacheEnabled())->toBeTrue();

    $builder->setCacheEnabled(false);

    expect($builder->getCacheEnabled())->toBeFalse();
});

it('can set cache TTL', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->setCacheTtl([600, 1200]);

    expect($builder->getCacheTtl())->toBe([600, 1200]);
});

it('uses default TTL from config when not set', function () {
    config(['stretch.cache.ttl' => [120, 240]]);

    $builder = new ElasticsearchQueryBuilder;

    expect($builder->getCacheTtl())->toBe([120, 240]);
});

it('can set cache prefix', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->setCachePrefix('search:');

    expect($builder->getCachePrefix())->toBe('search:');
});

it('uses default prefix from config when not set', function () {
    config(['stretch.cache.prefix' => 'es:']);

    $builder = new ElasticsearchQueryBuilder;

    expect($builder->getCachePrefix())->toBe('es:');
});

it('can set cache store', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->setCacheStore('redis');

    expect($builder->getCacheStore())->toBe('redis');
});

it('uses default store from config when not set', function () {
    config(['stretch.cache.store' => 'file']);

    $builder = new ElasticsearchQueryBuilder;

    expect($builder->getCacheStore())->toBe('file');
});

it('can enable cache clearing', function () {
    $builder = new ElasticsearchQueryBuilder;

    expect($builder->getCacheClear())->toBeFalse();

    $builder->clearCache();

    expect($builder->getCacheClear())->toBeTrue();
});

it('can set cache clear with setCacheClear', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->setCacheClear(true);

    expect($builder->getCacheClear())->toBeTrue();

    $builder->setCacheClear(false);

    expect($builder->getCacheClear())->toBeFalse();
});

it('generates consistent cache keys for same queries', function () {
    $builder1 = new ElasticsearchQueryBuilder;
    $builder1->index('test_index')
        ->match('title', 'Laravel')
        ->term('status', 'published');

    $builder2 = new ElasticsearchQueryBuilder;
    $builder2->index('test_index')
        ->match('title', 'Laravel')
        ->term('status', 'published');

    expect($builder1->getCacheKey())->toBe($builder2->getCacheKey());
});

it('generates different cache keys for different queries', function () {
    $builder1 = new ElasticsearchQueryBuilder;
    $builder1->index('test_index')
        ->match('title', 'Laravel');

    $builder2 = new ElasticsearchQueryBuilder;
    $builder2->index('test_index')
        ->match('title', 'Symfony');

    expect($builder1->getCacheKey())->not->toBe($builder2->getCacheKey());
});

it('includes index name in cache key', function () {
    $builder = new ElasticsearchQueryBuilder;
    $builder->index('products')
        ->match('name', 'test');

    $key = $builder->getCacheKey();

    expect($key)->toContain('products');
});

it('includes prefix in cache key', function () {
    $builder = new ElasticsearchQueryBuilder;
    $builder->setCachePrefix('search:')
        ->index('products')
        ->match('name', 'test');

    $key = $builder->getCacheKey();

    expect($key)->toStartWith('search:');
});

it('getIndexes returns single index for query builder', function () {
    $builder = new ElasticsearchQueryBuilder;
    $builder->index('products');

    $indexes = $builder->getIndexes();

    expect($indexes->toArray())->toBe(['products']);
});

it('getIndexes returns multiple indexes for multi query builder', function () {
    $multiBuilder = new MultiQueryBuilder;

    $multiBuilder->add('products_query', fn ($q) => $q->index('products')->match('name', 'test'));
    $multiBuilder->add('categories_query', fn ($q) => $q->index('categories')->match('title', 'electronics'));

    $indexes = $multiBuilder->getIndexes();

    expect($indexes->toArray())->toContain('products');
    expect($indexes->toArray())->toContain('categories');
});

it('getIndexes returns unique indexes for multi query builder', function () {
    $multiBuilder = new MultiQueryBuilder;

    $multiBuilder->add('products_query_1', fn ($q) => $q->index('products')->match('name', 'test'));
    $multiBuilder->add('products_query_2', fn ($q) => $q->index('products')->match('name', 'another'));
    $multiBuilder->add('categories_query', fn ($q) => $q->index('categories')->match('title', 'electronics'));

    $indexes = $multiBuilder->getIndexes();

    // Should only have 2 unique indexes: products and categories
    expect($indexes->count())->toBe(2);
});

it('__call method returns callback result for non-execute methods', function () {
    $builder = new class extends ElasticsearchQueryBuilder
    {
        public function callMagic(string $name, array $arguments)
        {
            return $this->__call($name, $arguments);
        }

        public function customMethod(): string
        {
            return 'custom_result';
        }
    };

    $builder->cache();

    $result = $builder->callMagic('customMethod', []);

    expect($result)->toBe('custom_result');
});

it('clears cache when clearCache is called', function () {
    $builder = new ElasticsearchQueryBuilder;
    $builder->index('test_index')
        ->match('title', 'Laravel')
        ->clearCache();

    Cache::shouldReceive('store')
        ->with('default')
        ->andReturnSelf();

    Cache::shouldReceive('forget')
        ->once();

    $builder->getCacheKey($builder->getCacheClear());
});

it('executes without caching when cache is disabled', function () {
    $mockClient = Mockery::mock(ClientContract::class);
    $mockClient->shouldReceive('search')
        ->once()
        ->andReturn(['hits' => ['total' => ['value' => 1]]]);

    $builder = new ElasticsearchQueryBuilder($mockClient);
    $builder->index('test_index')
        ->match('title', 'Laravel');

    $result = $builder->execute();

    expect($result)->toBe(['hits' => ['total' => ['value' => 1]]]);
});

it('supports method chaining for all cache configuration', function () {
    $builder = new ElasticsearchQueryBuilder;

    $result = $builder
        ->index('test_index')
        ->match('title', 'Laravel')
        ->cache()
        ->setCacheTtl([600, 1200])
        ->setCachePrefix('search:')
        ->setCacheStore('redis')
        ->clearCache();

    expect($result)->toBeInstanceOf(ElasticsearchQueryBuilder::class);
    expect($result->isCacheEnabled())->toBeTrue();
    expect($result->getCacheTtl())->toBe([600, 1200]);
    expect($result->getCachePrefix())->toBe('search:');
    expect($result->getCacheStore())->toBe('redis');
    expect($result->getCacheClear())->toBeTrue();
});

it('multi query builder can use caching', function () {
    $multiBuilder = new MultiQueryBuilder;

    $multiBuilder
        ->add('products', fn ($q) => $q->match('name', 'test'))
        ->cache()
        ->setCacheTtl([300, 600]);

    expect($multiBuilder->isCacheEnabled())->toBeTrue();
    expect($multiBuilder->getCacheTtl())->toBe([300, 600]);
});
