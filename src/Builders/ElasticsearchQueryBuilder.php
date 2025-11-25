<?php

declare(strict_types=1);

namespace JayI\Stretch\Builders;

use JayI\Stretch\Client\ElasticsearchClient;
use JayI\Stretch\Contracts\BoolQueryBuilderContract;
use JayI\Stretch\Contracts\ClientContract;
use JayI\Stretch\Contracts\QueryBuilderContract;
use JayI\Stretch\Contracts\RangeQueryBuilderContract;
use JayI\Stretch\ElasticsearchManager;

/**
 * ElasticsearchQueryBuilder provides a fluent interface for building Elasticsearch queries.
 *
 * This class implements the QueryBuilderContract and provides methods for building
 * complex Elasticsearch queries with support for multiple query types, aggregations,
 * sorting, pagination, and multi-connection support.
 */
class ElasticsearchQueryBuilder implements QueryBuilderContract
{
    protected array $query = [];

    protected array $aggregations = [];

    protected array $sort = [];

    protected array|string|bool|null $source = null;

    protected array $highlight = [];

    protected string|array|null $index = null;

    protected ?int $size = null;

    protected ?int $from = null;

    protected array $filters = [];

    /**
     * Create a new ElasticsearchQueryBuilder instance.
     *
     * @param  ClientContract|null  $client  The Elasticsearch client for query execution
     * @param  ElasticsearchManager|null  $manager  The connection manager for multi-connection support
     */
    public function __construct(
        protected ?ClientContract $client = null,
        protected ?ElasticsearchManager $manager = null
    ) {}

    public function index(string|array $index): static
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Switch to a specific Elasticsearch connection.
     *
     * Creates a new query builder instance using the specified connection name.
     * This allows building queries against different Elasticsearch clusters or configurations.
     *
     * @param  string  $name  The connection name as defined in configuration
     * @return static A new query builder instance using the specified connection
     *
     * @throws \RuntimeException If the manager is not available
     *
     * @example
     * ```php
     * Stretch::query()
     *     ->connection('logs')
     *     ->match('level', 'error')
     *     ->execute();
     * ```
     */
    public function connection(string $name): static
    {
        if (! $this->manager) {
            throw new \RuntimeException('Elasticsearch manager not available. Cannot switch connections.');
        }

        $client = new ElasticsearchClient($this->manager->connection($name));

        return new static($client, $this->manager);
    }

    public function match(string $field, mixed $value, array $options = []): static
    {
        $match = array_merge(['query' => $value], $options);

        $this->addQueryProtected([
            'match' => [
                $field => $match,
            ],
        ]);

        return $this;
    }

    public function matchPhrase(string $field, mixed $value, array $options = []): static
    {
        $match = array_merge(['query' => $value], $options);

        $this->addQueryProtected([
            'match_phrase' => [
                $field => $match,
            ],
        ]);

        return $this;
    }

    public function term(string $field, mixed $value): static
    {
        $this->addQueryProtected([
            'term' => [
                $field => $value,
            ],
        ]);

        return $this;
    }

    public function terms(string $field, array $values): static
    {
        $this->addQueryProtected([
            'terms' => [
                $field => $values,
            ],
        ]);

        return $this;
    }

    public function range(string $field): RangeQueryBuilderContract
    {
        return new RangeQueryBuilder($this, $field);
    }

    public function bool(?callable $callback = null): BoolQueryBuilderContract
    {
        $boolBuilder = new BoolQueryBuilder($this);

        if ($callback) {
            $callback($boolBuilder);
            $this->addQueryProtected($boolBuilder->build());
        }

        return $boolBuilder;
    }

    public function nested(string $path, callable $callback): static
    {
        $nestedQuery = new ElasticsearchQueryBuilder($this->client, $this->manager);
        $callback($nestedQuery);

        $this->addQueryProtected([
            'nested' => [
                'path' => $path,
                'query' => $nestedQuery->build()['query'],
            ],
        ]);

        return $this;
    }

    public function wildcard(string $field, string $value): static
    {
        $this->addQueryProtected([
            'wildcard' => [
                $field => $value,
            ],
        ]);

        return $this;
    }

    public function fuzzy(string $field, mixed $value, array $options = []): static
    {
        $fuzzy = array_merge(['value' => $value], $options);

        $this->addQueryProtected([
            'fuzzy' => [
                $field => $fuzzy,
            ],
        ]);

        return $this;
    }

    public function exists(string $field): static
    {
        $this->addQueryProtected([
            'exists' => [
                'field' => $field,
            ],
        ]);

        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function from(int $from): static
    {
        $this->from = $from;

        return $this;
    }

    public function sort(string|array $field, string $direction = 'asc'): static
    {
        if (is_string($field)) {
            $this->sort[] = [$field => ['order' => $direction]];
        } else {
            $this->sort[] = $field;
        }

        return $this;
    }

    public function source(array|string|bool $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function highlight(array $fields, array $options = []): static
    {
        $this->highlight = array_merge($options, ['fields' => $fields]);

        return $this;
    }

    public function aggregation(string $name, callable $callback): static
    {
        $aggregationBuilder = new AggregationBuilder;
        $callback($aggregationBuilder);
        $this->aggregations[$name] = $aggregationBuilder->build();

        return $this;
    }

    public function filter(callable $callback): static
    {
        $filterQuery = new ElasticsearchQueryBuilder($this->client, $this->manager);
        $callback($filterQuery);
        $this->filters[] = $filterQuery->build()['query'];

        return $this;
    }

    public function build(): array
    {
        $body = [];

        // Build the main query
        if (! empty($this->query) || ! empty($this->filters)) {
            if (! empty($this->filters)) {
                // If we have filters, wrap everything in a bool query
                $boolQuery = ['bool' => []];

                if (! empty($this->query)) {
                    if (count($this->query) === 1) {
                        $boolQuery['bool']['must'] = $this->query[0];
                    } else {
                        $boolQuery['bool']['must'] = $this->query;
                    }
                }

                $boolQuery['bool']['filter'] = $this->filters;
                $body['query'] = $boolQuery;
            } else {
                if (count($this->query) === 1) {
                    $body['query'] = $this->query[0];
                } else {
                    $body['query'] = [
                        'bool' => [
                            'must' => $this->query,
                        ],
                    ];
                }
            }
        }

        // Add other parameters
        if ($this->size !== null) {
            $body['size'] = $this->size;
        }

        if ($this->from !== null) {
            $body['from'] = $this->from;
        }

        if (! empty($this->sort)) {
            $body['sort'] = $this->sort;
        }

        if ($this->source !== null) {
            $body['_source'] = $this->source;
        }

        if (! empty($this->highlight)) {
            $body['highlight'] = $this->highlight;
        }

        if (! empty($this->aggregations)) {
            $body['aggs'] = $this->aggregations;
        }

        return $body;
    }

    public function execute(): array
    {
        if (! $this->client) {
            throw new \RuntimeException('Client not set. Cannot execute query.');
        }

        $body = $this->build();
        $params = [];

        if ($this->index) {
            $params['index'] = $this->index;
        }

        if (! empty($body)) {
            $params['body'] = $body;
        }

        return $this->client->search($params);
    }

    public function toArray(): array
    {
        return $this->build();
    }

    public function addQuery(array $query): void
    {
        $this->query[] = $query;
    }

    public function updateLastRangeQuery(string $field, array $rangeQuery): void
    {
        // Find and update the last range query for this field
        for ($i = count($this->query) - 1; $i >= 0; $i--) {
            if (isset($this->query[$i]['range'][$field])) {
                $this->query[$i] = $rangeQuery;
                break;
            }
        }
    }

    protected function addQueryProtected(array $query): void
    {
        $this->query[] = $query;
    }
}
