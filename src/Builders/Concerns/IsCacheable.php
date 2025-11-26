<?php

declare(strict_types=1);

namespace JayI\Stretch\Builders\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait IsCacheable
{
    protected bool $cacheEnabled = false;

    protected bool $cacheClear = false;
    protected ?array $cacheTtl = null;

    protected ?string $cachePrefix = null;

    protected ?string $cacheDriver = null;


    public function cache(): self
    {
        return $this->setCacheEnabled();
    }

    public function clearCache(): self
    {
        return $this->setCacheClear();
    }

    public function isCacheEnabled(): bool
    {
        return $this->getCacheEnabled();
    }

    /**
     * @param bool $cacheEnabled
     */
    public function setCacheEnabled(bool $cacheEnabled = true): self
    {
        $this->cacheEnabled = $cacheEnabled;
        return $this;
    }

    /**
     * @param bool $cacheEnabled
     */
    public function getCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    public function setCacheClear(bool $clear = true): self
    {
        $this->cacheClear = $clear;

        return $this;
    }

    public function getCacheClear(): bool
    {
        return $this->cacheClear;
    }

    public function setCacheTtl(array $ttl): self
    {
        $this->cacheTtl = $ttl;

        return $this;
    }

    /**
     * @return array
     */
    public function getCacheTtl(): array
    {
        return $this->cacheTtl ?? config('stretch.cache.ttl', [300,600]);
    }

    public function setCachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;
        return $this;
    }

    public function getCachePrefix(): string
    {
        return $this->cachePrefix ?? config('stretch.cache.prefix', '');
    }


    public function setCacheDriver(string $driver): self
    {
        $this->cacheDriver = $driver;
        return $this;
    }

    public function getCacheDriver(): string
    {
        return $this->cacheDriver ?? config('stretch.cache.driver', 'default');
    }

    public function getIndexes(): Collection
    {
        $indexes = collect([]);

        if (property_exists($this, 'index')) {
            $indexes = $indexes->push($this->index);
        }

        if(property_exists($this, 'queries')){
            $indexes = collect($this->queries)->pluck('index')->unique();;
        }

        return $indexes;
    }

    public function getCacheKey(bool $clear = false): string
    {
        $sorted = Arr::sortRecursive($this->build());
        $hash = sha1(serialize($sorted));
        $indexes = $this->getIndexes()->implode(':');

        $key = $this->getCachePrefix() . $indexes . $hash;

        if($clear) {
            Cache::driver($this->getCacheDriver())->forget($key);
        }

        return $key;
    }

    /**
     * @return bool
     */
    public function __call(string $name, array $arguments)
    {
        $callback = fn() => call_user_func_array([$this, $name], $arguments);

        return when(
            condition: $this->isCacheEnabled() && ($name == 'execute'),
            value: fn() => Cache::driver($this->getCacheDriver())->flexible(
                key: $this->getCacheKey($this->getCacheClear()),
                ttl: $this->getCacheTtl(),
                callback: $callback
            ),
            default: $callback,
        );

    }

}