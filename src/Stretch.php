<?php

declare(strict_types=1);

namespace JayI\Stretch;

use JayI\Stretch\Client\ElasticsearchClient;
use JayI\Stretch\Contracts\ClientContract;
use JayI\Stretch\Contracts\QueryBuilderContract;
use JayI\Stretch\Builders\ElasticsearchQueryBuilder;

/**
 * Stretch - Laravel Elasticsearch Query Builder
 *
 * The main entry point for building and executing Elasticsearch queries.
 * Provides fluent API for query building, index management, and multi-connection support.
 */
class Stretch
{
    /**
     * Create a new Stretch instance.
     *
     * @param  ClientContract  $client  The Elasticsearch client instance
     * @param  ElasticsearchManager|null  $manager  The connection manager for multi-connection support
     */
    public function __construct(
        protected ClientContract $client,
        protected ?ElasticsearchManager $manager = null
    ) {}

    /**
     * Create a new query builder instance.
     *
     * Returns a query builder configured with the current client and manager.
     *
     * @return QueryBuilderContract A new query builder instance
     */
    public function query(): QueryBuilderContract
    {
        return new ElasticsearchQueryBuilder($this->client, $this->manager);
    }

    /**
     * Switch to a specific Elasticsearch connection.
     *
     * Creates a new Stretch instance using the specified connection name.
     * This allows you to use different Elasticsearch clusters or configurations
     * within the same application.
     *
     * @param  string  $name  The connection name as defined in configuration
     * @return static A new Stretch instance using the specified connection
     *
     * @throws \RuntimeException If the manager is not available
     *
     * @example
     * ```php
     * Stretch::connection('analytics')->query()->match('event', 'click')->execute();
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

    /**
     * Create a new query builder for a specific index
     */
    public function index(string|array $index): QueryBuilderContract
    {
        return $this->query()->index($index);
    }

    /**
     * Get the underlying Elasticsearch client
     */
    public function client(): ClientContract
    {
        return $this->client;
    }

    /**
     * Check if an index exists
     */
    public function indexExists(string $index): bool
    {
        return $this->client->indexExists($index);
    }

    /**
     * Create an index
     */
    public function createIndex(string $index, array $settings = []): array
    {
        return $this->client->createIndex($index, $settings);
    }

    /**
     * Delete an index
     */
    public function deleteIndex(string $index): array
    {
        return $this->client->deleteIndex($index);
    }

    /**
     * Get cluster health
     */
    public function health(): array
    {
        return $this->client->health();
    }

    /**
     * Get all indices
     */
    public function indices(): array
    {
        return $this->client->indices();
    }

    /**
     * Perform bulk operations
     */
    public function bulk(array $operations): array
    {
        return $this->client->bulk(['body' => $operations]);
    }

    /**
     * Index a document
     */
    public function indexDocument(string $index, array $document, ?string $id = null): array
    {
        $params = [
            'index' => $index,
            'body' => $document,
        ];

        if ($id) {
            $params['id'] = $id;
        }

        return $this->client->index($params);
    }

    /**
     * Update a document
     */
    public function updateDocument(string $index, string $id, array $document): array
    {
        return $this->client->update([
            'index' => $index,
            'id' => $id,
            'body' => [
                'doc' => $document,
            ],
        ]);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(string $index, string $id): array
    {
        return $this->client->delete([
            'index' => $index,
            'id' => $id,
        ]);
    }

    /**
     * Get a document by ID
     */
    public function getDocument(string $index, string $id): array
    {
        return $this->client->get([
            'index' => $index,
            'id' => $id,
        ]);
    }
}
