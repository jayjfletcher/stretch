<?php

declare(strict_types=1);

namespace JayI\Stretch\Contracts;

interface RangeQueryBuilderContract
{
    /**
     * Set greater than
     */
    public function gt(mixed $value): static;

    /**
     * Set greater than or equal
     */
    public function gte(mixed $value): static;

    /**
     * Set less than
     */
    public function lt(mixed $value): static;

    /**
     * Set less than or equal
     */
    public function lte(mixed $value): static;

    /**
     * Set timezone for date ranges
     */
    public function timezone(string $timezone): static;

    /**
     * Set format for date ranges
     */
    public function format(string $format): static;

    /**
     * Get the parent query builder
     */
    public function getParent(): QueryBuilderContract;

    /**
     * Build the range query
     */
    public function build(): array;
}
