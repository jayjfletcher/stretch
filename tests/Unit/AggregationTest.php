<?php

declare(strict_types=1);

use JayI\Stretch\Builders\AggregationBuilder;

it('can create a terms aggregation', function () {
    $agg = new AggregationBuilder;

    $agg->terms('category.keyword');

    $built = $agg->build();

    expect($built['terms']['field'])->toBe('category.keyword');
});

it('can create a date histogram aggregation', function () {
    $agg = new AggregationBuilder;

    $agg->dateHistogram('created_at', 'month');

    $built = $agg->build();

    expect($built['date_histogram']['field'])->toBe('created_at');
    expect($built['date_histogram']['calendar_interval'])->toBe('month');
});

it('can create metric aggregations', function () {
    $avgAgg = new AggregationBuilder;
    $avgAgg->avg('price');
    expect($avgAgg->build()['avg']['field'])->toBe('price');

    $sumAgg = new AggregationBuilder;
    $sumAgg->sum('quantity');
    expect($sumAgg->build()['sum']['field'])->toBe('quantity');

    $minAgg = new AggregationBuilder;
    $minAgg->min('rating');
    expect($minAgg->build()['min']['field'])->toBe('rating');

    $maxAgg = new AggregationBuilder;
    $maxAgg->max('score');
    expect($maxAgg->build()['max']['field'])->toBe('score');
});

it('can set aggregation size', function () {
    $agg = new AggregationBuilder;

    $agg->terms('category.keyword')->size(5);

    $built = $agg->build();

    expect($built['terms']['size'])->toBe(5);
});

it('can add sub-aggregations', function () {
    $agg = new AggregationBuilder;

    $agg->terms('category.keyword')
        ->subAggregation('avg_price', fn ($sub) => $sub->avg('price'));

    $built = $agg->build();

    expect($built['aggs']['avg_price']['avg']['field'])->toBe('price');
});

it('can create range aggregation', function () {
    $agg = new AggregationBuilder;

    $ranges = [
        ['to' => 100],
        ['from' => 100, 'to' => 200],
        ['from' => 200],
    ];

    $agg->range('price', $ranges);

    $built = $agg->build();

    expect($built['range']['field'])->toBe('price');
    expect($built['range']['ranges'])->toBe($ranges);
});

it('can create histogram aggregation', function () {
    $agg = new AggregationBuilder;

    $agg->histogram('price', 50);

    $built = $agg->build();

    expect($built['histogram']['field'])->toBe('price');
    expect($built['histogram']['interval'])->toBe(50);
});

it('can create cardinality aggregation', function () {
    $agg = new AggregationBuilder;

    $agg->cardinality('user_id');

    $built = $agg->build();

    expect($built['cardinality']['field'])->toBe('user_id');
});

it('can add ordering to terms aggregation', function () {
    $agg = new AggregationBuilder;

    $agg->terms('category.keyword')->orderBy('_count', 'desc');

    $built = $agg->build();

    expect($built['terms']['order']['_count']['order'])->toBe('desc');
});

it('can create top hits aggregation with default size', function () {
    $agg = new AggregationBuilder;

    $agg->topHits();

    $built = $agg->build();

    expect($built['top_hits']['size'])->toBe(100);
});

it('can create top hits aggregation with custom size', function () {
    $agg = new AggregationBuilder;

    $agg->topHits(50);

    $built = $agg->build();

    expect($built['top_hits']['size'])->toBe(50);
});
