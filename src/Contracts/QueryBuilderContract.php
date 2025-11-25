<?php

declare(strict_types=1);

namespace JayI\Stretch\Contracts;

interface QueryBuilderContract
{
    /**
     * Set the index to search
     */
    public function index(string|array $index): static;

    /**
     * Switch to a specific Elasticsearch connection.
     *
     * Creates a new query builder instance using the specified connection name.
     * This allows building queries against different Elasticsearch clusters
     * or configurations within the same application.
     *
     * @param  string  $name  The connection name as defined in configuration
     * @return static A new query builder instance using the specified connection
     *
     * @throws \RuntimeException If the connection manager is not available
     */
    public function connection(string $name): static;

    /**
     * Add a match query
     */
    public function match(string $field, mixed $value, array $options = []): static;

    /**
     * Add a match phrase query
     */
    public function matchPhrase(string $field, mixed $value, array $options = []): static;

    /**
     * Add a term query
     */
    public function term(string $field, mixed $value): static;

    /**
     * Add a terms query
     */
    public function terms(string $field, array $values): static;

    /**
     * Add a range query
     */
    public function range(string $field): RangeQueryBuilderContract;

    /**
     * Add a bool query
     */
    public function bool(?callable $callback = null): BoolQueryBuilderContract;

    /**
     * Add a nested query
     */
    public function nested(string $path, callable $callback): static;

    /**
     * Add a wildcard query
     */
    public function wildcard(string $field, string $value): static;

    /**
     * Add a fuzzy query
     */
    public function fuzzy(string $field, mixed $value, array $options = []): static;

    /**
     * Add an exists query
     */
    public function exists(string $field): static;

    /**
     * Set the size (limit) of results
     */
    public function size(int $size): static;

    /**
     * Set the from (offset) for pagination
     */
    public function from(int $from): static;

    /**
     * Add sorting
     */
    public function sort(string|array $field, string $direction = 'asc'): static;

    /**
     * Add source filtering
     */
    public function source(array|string|bool $source): static;

    /**
     * Add highlighting
     */
    public function highlight(array $fields, array $options = []): static;

    /**
     * Add an aggregation
     */
    public function aggregation(string $name, callable $callback): static;

    /**
     * Add a filter context query
     */
    public function filter(callable $callback): static;

    /**
     * Build the final query array
     */
    public function build(): array;

    /**
     * Execute the query and return results
     */
    public function execute(): array;

    /**
     * Get the raw query array for debugging
     */
    public function toArray(): array;
}
