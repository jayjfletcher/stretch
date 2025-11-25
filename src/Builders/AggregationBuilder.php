<?php

declare(strict_types=1);

namespace JayI\Stretch\Builders;

use JayI\Stretch\Contracts\AggregationBuilderContract;

class AggregationBuilder implements AggregationBuilderContract
{
    protected array $aggregation = [];

    protected array $subAggregations = [];

    protected ?int $size = null;

    protected array $order = [];

    public function terms(string $field): static
    {
        $this->aggregation = [
            'terms' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    public function dateHistogram(string $field, string $interval): static
    {
        $this->aggregation = [
            'date_histogram' => [
                'field' => $field,
                'calendar_interval' => $interval,
            ],
        ];

        return $this;
    }

    public function avg(string $field): static
    {
        $this->aggregation = [
            'avg' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    public function sum(string $field): static
    {
        $this->aggregation = [
            'sum' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    public function min(string $field): static
    {
        $this->aggregation = [
            'min' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    public function max(string $field): static
    {
        $this->aggregation = [
            'max' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    public function count(): static
    {
        $this->aggregation = [
            'value_count' => [
                'field' => '_id',
            ],
        ];

        return $this;
    }

    public function cardinality(string $field): static
    {
        $this->aggregation = [
            'cardinality' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    public function range(string $field, array $ranges): static
    {
        $this->aggregation = [
            'range' => [
                'field' => $field,
                'ranges' => $ranges,
            ],
        ];

        return $this;
    }

    public function histogram(string $field, int|float $interval): static
    {
        $this->aggregation = [
            'histogram' => [
                'field' => $field,
                'interval' => $interval,
            ],
        ];

        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function subAggregation(string $name, callable $callback): static
    {
        $subAggBuilder = new self;
        $callback($subAggBuilder);
        $this->subAggregations[$name] = $subAggBuilder->build();

        return $this;
    }

    public function orderBy(string $field, string $direction = 'asc'): static
    {
        $this->order = [
            $field => [
                'order' => $direction,
            ],
        ];

        return $this;
    }

    public function build(): array
    {
        $agg = $this->aggregation;

        // Add size if specified and this is a bucket aggregation
        if ($this->size !== null && isset($agg['terms'])) {
            $agg['terms']['size'] = $this->size;
        }

        // Add order if specified and this is a bucket aggregation
        if (! empty($this->order) && isset($agg['terms'])) {
            $agg['terms']['order'] = $this->order;
        }

        // Add sub-aggregations
        if (! empty($this->subAggregations)) {
            $agg['aggs'] = $this->subAggregations;
        }

        return $agg;
    }
}
