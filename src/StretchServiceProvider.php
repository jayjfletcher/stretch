<?php

declare(strict_types=1);

namespace JayI\Stretch;

use Elastic\Elasticsearch\Client;
use JayI\Stretch\Client\ElasticsearchClient;
use JayI\Stretch\Contracts\ClientContract;
use JayI\Stretch\Contracts\QueryBuilderContract;
use JayI\Stretch\Builders\ElasticsearchQueryBuilder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * StretchServiceProvider handles the registration of Stretch services.
 *
 * This service provider registers all necessary bindings for the Stretch package
 * including the Elasticsearch manager, clients, query builders, and the main
 * Stretch facade. It supports multi-connection functionality and proper
 * dependency injection throughout the package.
 */
class StretchServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package settings.
     *
     * Sets up the package name and configuration file publication.
     *
     * @param  Package  $package  The package configuration instance
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('stretch')
            ->hasConfigFile();
    }

    /**
     * Register package services after the package is registered.
     *
     * This method is called automatically by the parent class and handles
     * the registration of all Stretch-related services in the container.
     */
    public function packageRegistered(): void
    {
        $this->registerElasticsearchClient();
        $this->registerQueryBuilder();
        $this->registerStretch();
    }

    /**
     * Register Elasticsearch client services.
     *
     * Registers the ElasticsearchManager for multi-connection support,
     * the default Elasticsearch client, and the ClientContract binding.
     * This setup enables both single and multi-connection usage patterns.
     */
    protected function registerElasticsearchClient(): void
    {
        // Register the Elasticsearch connection manager as a singleton
        // This manager handles multiple named connections and lazy loading
        $this->app->singleton('elasticsearch.manager', function ($app): ElasticsearchManager {
            return new ElasticsearchManager($app);
        });

        // Register the default Elasticsearch client
        // Uses the connection manager to get the default connection
        $this->app->singleton(Client::class, function ($app): Client {
            return $app['elasticsearch.manager']->connection();
        });

        // Register the client contract binding
        // Wraps the native Elasticsearch client with our contract implementation
        $this->app->singleton(ClientContract::class, function ($app): ClientContract {
            return new ElasticsearchClient($app[Client::class]);
        });
    }

    /**
     * Register the query builder contract binding.
     *
     * Binds the QueryBuilderContract to the ElasticsearchQueryBuilder
     * implementation for dependency injection support.
     */
    protected function registerQueryBuilder(): void
    {
        $this->app->bind(QueryBuilderContract::class, ElasticsearchQueryBuilder::class);
    }

    /**
     * Register the main Stretch service.
     *
     * Registers the Stretch class as a singleton with both the default
     * client and the connection manager for multi-connection support.
     * This enables both the Stretch facade and dependency injection usage.
     */
    protected function registerStretch(): void
    {
        // Register the main Stretch service as a singleton
        // Inject both the client contract and manager for full functionality
        $this->app->singleton('stretch', function ($app): Stretch {
            return new Stretch(
                $app[ClientContract::class],
                $app['elasticsearch.manager']
            );
        });
    }
}
