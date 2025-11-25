<?php

declare(strict_types=1);

namespace JayI\Stretch\Facades;

use Illuminate\Support\Facades\Facade;
use JayI\Stretch\Contracts\ClientContract;
use JayI\Stretch\Contracts\QueryBuilderContract;

/**
 * @method static QueryBuilderContract query()
 * @method static QueryBuilderContract index(string|array $index)
 * @method static \JayI\Stretch\Stretch connection(string $name)
 * @method static ClientContract client()
 * @method static bool indexExists(string $index)
 * @method static array createIndex(string $index, array $settings = [])
 * @method static array deleteIndex(string $index)
 * @method static array health()
 * @method static array indices()
 * @method static array bulk(array $operations)
 * @method static array indexDocument(string $index, array $document, ?string $id = null)
 * @method static array updateDocument(string $index, string $id, array $document)
 * @method static array deleteDocument(string $index, string $id)
 * @method static array getDocument(string $index, string $id)
 *
 * @see \JayI\Stretch\Stretch
 */
class Stretch extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'stretch';
    }
}
