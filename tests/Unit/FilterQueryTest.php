<?php

declare(strict_types=1);

use JayI\Stretch\Builders\ElasticsearchQueryBuilder;

it('can create a standalone filter query', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->filter(function ($q) {
        $q->term('status', 'published');
    });

    $query = $builder->build();

    expect($query)->toHaveKey('query')
        ->and($query['query'])->toHaveKey('bool')
        ->and($query['query']['bool'])->toHaveKey('filter')
        ->and($query['query']['bool']['filter'])->toHaveCount(1);
});

it('can create filter query on specific field', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->filter(function ($q) {
        $q->term('customerAccountNumber.keyword', '191954');
    });

    $query = $builder->build();

    expect($query)->toHaveKey('query')
        ->and($query['query']['bool']['filter'][0])->toHaveKey('term')
        ->and($query['query']['bool']['filter'][0]['term']['customerAccountNumber.keyword'])->toBe('191954');
});

it('can combine match query with filter', function () {
    $builder = new ElasticsearchQueryBuilder;

    $builder->match('title', 'Laravel')
        ->filter(function ($q) {
            $q->term('status', 'published');
        });

    $query = $builder->build();

    expect($query)->toHaveKey('query')
        ->and($query['query']['bool'])->toHaveKey('must')
        ->and($query['query']['bool'])->toHaveKey('filter');
});
