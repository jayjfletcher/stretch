<?php

declare(strict_types=1);

namespace JayI\Stretch\Client;

use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use JayI\Stretch\Contracts\ClientContract;
use JayI\Stretch\Exceptions\StretchException;

class ElasticsearchClient implements ClientContract
{

    public function __construct(
        protected Client $client
    ) {}

    public function search(array $params): array
    {
        try {
            $this->logQuery($params);

            $response = $this->cacheQueryResults($params);

            $this->logSlowQuery($params, $response);
            return $response;
        } catch (Exception $exception) {
            throw new StretchException("Search failed: {$exception->getMessage()}", 0, $exception);
        }
    }

    public function index(array $params): array
    {
        try {
            return $this->client->index($params)->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Index operation failed: {$exception->getMessage()}", 0, $exception);
        }
    }

    public function update(array $params): array
    {
        try {
            return $this->client->update($params)->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Update operation failed: {$exception->getMessage()}", 0, $exception);
        }
    }

    public function delete(array $params): array
    {
        try {
            return $this->client->delete($params)->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Delete operation failed: {$exception->getMessage()}", 0, $exception);
        }
    }

    public function bulk(array $params): array
    {
        try {
            return $this->client->bulk($params)->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Bulk operation failed: {$exception->getMessage()}", 0, $exception);
        }
    }

    public function indices(): array
    {
        try {
            return $this->client->indices()->get(['index' => '*'])->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Failed to get indices: {$exception->getMessage()}", 0, $exception);
        }
    }

    public function indexExists(string $index): bool
    {
        try {
            return $this->client->indices()->exists(['index' => $index])->asBool();
        } catch (Exception $exception) {
            return false;
        }
    }

    public function createIndex(string $index, array $settings = []): array
    {
        try {
            $params = ['index' => $index];
            if (! empty($settings)) {
                $params['body'] = $settings;
            }

            return $this->client->indices()->create($params)->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Failed to create index '{$index}': {$exception->getMessage()}", 0, $exception);
        }
    }

    public function deleteIndex(string $index): array
    {
        try {
            return $this->client->indices()->delete(['index' => $index])->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Failed to delete index '{$index}': {$exception->getMessage()}", 0, $exception);
        }
    }

    public function health(): array
    {
        try {
            return $this->client->cluster()->health()->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Failed to get cluster health: {$exception->getMessage()}", 0, $exception);
        }
    }

    public function get(array $params): array
    {
        try {
            return $this->client->get($params)->asArray();
        } catch (Exception $exception) {
            throw new StretchException("Get operation failed: {$exception->getMessage()}", 0, $exception);
        }
    }

    protected function logQuery(array $query): void
    {
        if(config('stretch.logging.log_queries')){
            $this->log('Elasticsearch query:',$query);
        }
    }

    protected function logSlowQuery(array $query, $response): void
    {
        if(config('stretch.logging.log_slow_queries')){
            $time = $response['took'] ?? 0;
            if($time > config('stretch.logging.slow_query_threshold')){
                $this->log('Slow Elasticsearch query:',$query, 'warning');
            }
        }
    }

    protected function log(string $message, array $context = [], $level = 'info'): void
    {
        if(config('stretch.logging.enabled')) {
            logger()->{$level}($message, $context);
        }
    }

    protected function cacheQueryResults(array $params): array
    {
        $callback = fn() => $this->client->search($params)->asArray();

        return when(
            condition: config('stretch.cache.enabled'),
            value: fn() => Cache::driver(config('stretch.cache.driver'))->flexible(
                key: config('stretch.cache.prefix') . Arr::hash($params),
                ttl: config('stretch.cache.ttl'),
                callback: $callback
            ),
            default: $callback,
        );
    }
}
