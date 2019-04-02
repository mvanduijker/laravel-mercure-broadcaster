<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster;

use Duijker\LaravelMercureBroadcaster\Broadcasting\Broadcasters\MercureBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\Mercure\Publisher;

class LaravelMercureBroadcasterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app
            ->make(BroadcastManager::class)
            ->extend('mercure', function ($app, array $config) {
                return new MercureBroadcaster(
                    new Publisher(
                        $config['url'],
                        function () use ($config) {
                            $token = (new Builder())
                                ->set('mercure', ['publish' => ['*']])
                                ->sign(new Sha256(), $config['secret'])
                                ->getToken();

                            return (string) $token;
                        }
                    )
                );
            });
    }

    public function register()
    {
    }
}
