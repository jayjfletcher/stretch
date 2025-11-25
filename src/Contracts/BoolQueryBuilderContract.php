<?php

declare(strict_types=1);

namespace JayI\Stretch\Contracts;

interface BoolQueryBuilderContract
{
    /**
     * Add must clauses (AND logic)
     */
    public function must(callable|array $callback): static;

    /**
     * Add should clauses (OR logic)
     */
    public function should(callable|array $callback): static;

    /**
     * Add filter clauses (AND logic, no scoring)
     */
    public function filter(callable|array $callback): static;

    /**
     * Add must_not clauses (NOT logic)
     */
    public function mustNot(callable|array $callback): static;

    /**
     * Set minimum should match
     */
    public function minimumShouldMatch(int|string $minimum): static;

    /**
     * Get the parent query builder
     */
    public function getParent(): QueryBuilderContract;

    /**
     * Build the bool query
     */
    public function build(): array;
}
