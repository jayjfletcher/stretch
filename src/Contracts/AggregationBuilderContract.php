<?php

declare(strict_types=1);

namespace JayI\Stretch\Contracts;

interface AggregationBuilderContract
{
    /**
     * Terms aggregation
     */
    public function terms(string $field): static;

    /**
     * Date histogram aggregation
     */
    public function dateHistogram(string $field, string $interval): static;

    /**
     * Average metric aggregation
     */
    public function avg(string $field): static;

    /**
     * Sum metric aggregation
     */
    public function sum(string $field): static;

    /**
     * Min metric aggregation
     */
    public function min(string $field): static;

    /**
     * Max metric aggregation
     */
    public function max(string $field): static;

    /**
     * Count aggregation
     */
    public function count(): static;

    /**
     * Cardinality aggregation
     */
    public function cardinality(string $field): static;

    /**
     * Range aggregation
     */
    public function range(string $field, array $ranges): static;

    /**
     * Histogram aggregation
     */
    public function histogram(string $field, int|float $interval): static;

    /**
     * Set aggregation size
     */
    public function size(int $size): static;

    /**
     * Add sub-aggregation
     */
    public function subAggregation(string $name, callable $callback): static;

    /**
     * Set order for bucket aggregations
     */
    public function orderBy(string $field, string $direction = 'asc'): static;

    /**
     * Build the aggregation
     */
    public function build(): array;
}
