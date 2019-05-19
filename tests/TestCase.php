<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Tests;

use Duijker\LaravelMercureBroadcaster\LaravelMercureBroadcasterServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('broadcasting.default', 'mercure');
        $app['config']->set('broadcasting.connections.mercure.driver', 'mercure');
        $app['config']->set('broadcasting.connections.mercure.url', 'http://localhost:3000/hub');
        $app['config']->set('broadcasting.connections.mercure.secret', 'aVerySecretKey');
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelMercureBroadcasterServiceProvider::class,
        ];
    }
}
