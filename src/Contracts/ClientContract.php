<?php

declare(strict_types=1);

namespace JayI\Stretch\Contracts;

interface ClientContract
{
    /**
     * Execute a search query
     */
    public function search(array $params): array;

    /**
     * Index a document
     */
    public function index(array $params): array;

    /**
     * Update a document
     */
    public function update(array $params): array;

    /**
     * Delete a document
     */
    public function delete(array $params): array;

    /**
     * Bulk operations
     */
    public function bulk(array $params): array;

    /**
     * Get index info
     */
    public function indices(): array;

    /**
     * Check if index exists
     */
    public function indexExists(string $index): bool;

    /**
     * Create an index
     */
    public function createIndex(string $index, array $settings = []): array;

    /**
     * Delete an index
     */
    public function deleteIndex(string $index): array;

    /**
     * Get cluster health
     */
    public function health(): array;

    /**
     * Get a document by ID
     */
    public function get(array $params): array;

    /**
     * Execute multiple search queries in a single request
     */
    public function msearch(array $params): array;
}
