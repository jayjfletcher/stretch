<?php

declare(strict_types=1);

namespace JayI\Stretch\Builders;

use JayI\Stretch\Builders\Concerns\IsCacheable;
use JayI\Stretch\Client\ElasticsearchClient;
use JayI\Stretch\Contracts\ClientContract;
use JayI\Stretch\Contracts\MultiQueryBuilderContract;
use JayI\Stretch\Contracts\QueryBuilderContract;
use JayI\Stretch\ElasticsearchManager;

/**
 * MultiQueryBuilder provides a fluent interface for building Elasticsearch multi-search requests.
 *
 * This class allows you to combine multiple search queries into a single request,
 * reducing network overhead when you need to execute several searches at once.
 */
class MultiQueryBuilder implements MultiQueryBuilderContract
{
    use IsCacheable;

    /**
     * The queries to be executed in the multi-search request.
     * Each entry contains 'index' and 'query' keys.
     *
     * @var array<int, array{index: string|array, query: QueryBuilderContract}>
     */
    protected array $queries = [];

    /**
     * Create a new MultiQueryBuilder instance.
     *
     * @param  ClientContract|null  $client  The Elasticsearch client for query execution
     * @param  ElasticsearchManager|null  $manager  The connection manager for multi-connection support
     */
    public function __construct(
        protected ?ClientContract $client = null,
        protected ?ElasticsearchManager $manager = null
    ) {}

    /**
     * Switch to a specific Elasticsearch connection.
     *
     * @param  string  $name  The connection name as defined in configuration
     * @return static A new multi-query builder instance using the specified connection
     *
     * @throws \RuntimeException If the manager is not available
     */
    public function connection(string $name): static
    {
        if (! $this->manager) {
            throw new \RuntimeException('Elasticsearch manager not available. Cannot switch connections.');
        }

        $client = new ElasticsearchClient($this->manager->connection($name));

        return new self($client, $this->manager);
    }

    public function add(string|array $index, callable|QueryBuilderContract $query): static
    {
        if (is_callable($query)) {
            $builder = new ElasticsearchQueryBuilder($this->client, $this->manager);
            $query($builder);
            $query = $builder;
        }

        $this->queries[] = [
            'index' => $index,
            'query' => $query,
        ];

        return $this;
    }

    public function build(): array
    {
        $body = [];

        foreach ($this->queries as $entry) {
            // Header line - specifies the index
            $header = [];
            if (is_array($entry['index'])) {
                $header['index'] = implode(',', $entry['index']);
            } else {
                $header['index'] = $entry['index'];
            }

            $body[] = $header;

            // Body line - the query
            $body[] = $entry['query']->build();
        }

        return $body;
    }

    public function execute(): array
    {
        if (! $this->client) {
            throw new \RuntimeException('Client not set. Cannot execute query.');
        }

        if (empty($this->queries)) {
            return ['responses' => []];
        }

        return $this->client->msearch(['body' => $this->build()]);
    }

    public function toArray(): array
    {
        return $this->build();
    }

    /**
     * Get the number of queries in the multi-search request.
     */
    public function count(): int
    {
        return count($this->queries);
    }
}
