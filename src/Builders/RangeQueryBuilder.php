<?php

declare(strict_types=1);

namespace JayI\Stretch\Builders;

use JayI\Stretch\Contracts\QueryBuilderContract;
use JayI\Stretch\Contracts\RangeQueryBuilderContract;

class RangeQueryBuilder implements RangeQueryBuilderContract
{
    protected array $conditions = [];

    protected ?string $timezone = null;

    protected ?string $format = null;

    protected bool $addedToParent = false;

    public function __construct(
        protected QueryBuilderContract $parent,
        protected string $field
    ) {}

    public function gt(mixed $value): static
    {
        $this->conditions['gt'] = $value;
        $this->addToParent();

        return $this;
    }

    public function gte(mixed $value): static
    {
        $this->conditions['gte'] = $value;
        $this->addToParent();

        return $this;
    }

    public function lt(mixed $value): static
    {
        $this->conditions['lt'] = $value;
        $this->addToParent();

        return $this;
    }

    public function lte(mixed $value): static
    {
        $this->conditions['lte'] = $value;
        $this->addToParent();

        return $this;
    }

    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        $this->addToParent();

        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;
        $this->addToParent();

        return $this;
    }

    public function getParent(): QueryBuilderContract
    {
        return $this->parent;
    }

    public function build(): array
    {
        $range = $this->conditions;

        if ($this->timezone) {
            $range['time_zone'] = $this->timezone;
        }

        if ($this->format) {
            $range['format'] = $this->format;
        }

        return [
            'range' => [
                $this->field => $range,
            ],
        ];
    }

    protected function addToParent(): void
    {
        if (! $this->addedToParent) {
            $this->getParent()->addQuery($this->build());
            $this->addedToParent = true;
        } else {
            // Update the existing range query in the parent
            $this->getParent()->updateLastRangeQuery($this->field, $this->build());
        }
    }
}
