<?php

declare(strict_types=1);

namespace JayI\Stretch\Exceptions;

use Exception;

/**
 * Base exception for all Stretch-related errors.
 *
 * This exception wraps errors from the underlying Elasticsearch client
 * and provides consistent error handling throughout the package.
 * The original exception is preserved as the previous exception for
 * debugging purposes.
 *
 * @example
 * ```php
 * try {
 *     $results = Stretch::index('posts')->match('title', 'test')->execute();
 * } catch (StretchException $e) {
 *     // Handle Elasticsearch errors
 *     logger()->error($e->getMessage(), ['previous' => $e->getPrevious()]);
 * }
 * ```
 */
class StretchException extends Exception
{
    //
}
