<?php

declare(strict_types=1);

namespace JayI\Stretch\Contracts;

/**
 * Contract for Elasticsearch range query builders.
 *
 * Defines the interface for building range queries with greater than,
 * less than, and their inclusive variants, plus timezone and format
 * options for date fields.
 */
interface RangeQueryBuilderContract
{
    /**
     * Set greater than condition (exclusive).
     *
     * @param  mixed  $value  The lower bound (exclusive)
     * @return static Returns the builder instance for method chaining
     */
    public function gt(mixed $value): static;

    /**
     * Set greater than or equal condition (inclusive).
     *
     * @param  mixed  $value  The lower bound (inclusive)
     * @return static Returns the builder instance for method chaining
     */
    public function gte(mixed $value): static;

    /**
     * Set less than condition (exclusive).
     *
     * @param  mixed  $value  The upper bound (exclusive)
     * @return static Returns the builder instance for method chaining
     */
    public function lt(mixed $value): static;

    /**
     * Set less than or equal condition (inclusive).
     *
     * @param  mixed  $value  The upper bound (inclusive)
     * @return static Returns the builder instance for method chaining
     */
    public function lte(mixed $value): static;

    /**
     * Set the timezone for date range queries.
     *
     * @param  string  $timezone  IANA timezone (e.g., "America/New_York")
     * @return static Returns the builder instance for method chaining
     */
    public function timezone(string $timezone): static;

    /**
     * Set the date format for parsing date strings.
     *
     * @param  string  $format  Elasticsearch date format pattern
     * @return static Returns the builder instance for method chaining
     */
    public function format(string $format): static;

    /**
     * Get the parent query builder.
     *
     * @return QueryBuilderContract The parent query builder instance
     */
    public function getParent(): QueryBuilderContract;

    /**
     * Build the range query array.
     *
     * @return array The Elasticsearch range query structure
     */
    public function build(): array;
}
