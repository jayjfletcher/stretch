<?php

declare(strict_types=1);

namespace JayI\Stretch\Builders;

use JayI\Stretch\Contracts\BoolQueryBuilderContract;
use JayI\Stretch\Contracts\QueryBuilderContract;

class BoolQueryBuilder implements BoolQueryBuilderContract
{
    protected array $must = [];

    protected array $should = [];

    protected array $filter = [];

    protected array $mustNot = [];

    protected int|string|null $minimumShouldMatch = null;

    public function __construct(
        protected QueryBuilderContract $parent
    ) {}

    public function must(callable|array $callback): static
    {
        if (is_callable($callback)) {
            $queryBuilder = new ElasticsearchQueryBuilder;
            $callback($queryBuilder);
            $this->must[] = $queryBuilder->build()['query'];
        } elseif (is_array($callback)) {
            foreach ($callback as $cb) {
                $this->must($cb);
            }
        }

        return $this;
    }

    public function should(callable|array $callback): static
    {
        if (is_callable($callback)) {
            $queryBuilder = new ElasticsearchQueryBuilder;
            $callback($queryBuilder);
            $this->should[] = $queryBuilder->build()['query'];
        } elseif (is_array($callback)) {
            foreach ($callback as $cb) {
                $this->should($cb);
            }
        }

        return $this;
    }

    public function filter(callable|array $callback): static
    {
        if (is_callable($callback)) {
            $queryBuilder = new ElasticsearchQueryBuilder;
            $callback($queryBuilder);
            $this->filter[] = $queryBuilder->build()['query'];
        } elseif (is_array($callback)) {
            foreach ($callback as $cb) {
                $this->filter($cb);
            }
        }

        return $this;
    }

    public function mustNot(callable|array $callback): static
    {
        if (is_callable($callback)) {
            $queryBuilder = new ElasticsearchQueryBuilder;
            $callback($queryBuilder);
            $this->mustNot[] = $queryBuilder->build()['query'];
        } elseif (is_array($callback)) {
            foreach ($callback as $cb) {
                $this->mustNot($cb);
            }
        }

        return $this;
    }

    public function minimumShouldMatch(int|string $minimum): static
    {
        $this->minimumShouldMatch = $minimum;

        return $this;
    }

    public function getParent(): QueryBuilderContract
    {
        return $this->parent;
    }

    public function build(): array
    {
        $bool = [];

        if (! empty($this->must)) {
            $bool['must'] = $this->must;
        }

        if (! empty($this->should)) {
            $bool['should'] = $this->should;
        }

        if (! empty($this->filter)) {
            $bool['filter'] = $this->filter;
        }

        if (! empty($this->mustNot)) {
            $bool['must_not'] = $this->mustNot;
        }

        if ($this->minimumShouldMatch !== null) {
            $bool['minimum_should_match'] = $this->minimumShouldMatch;
        }

        return [
            'bool' => $bool,
        ];
    }
}
