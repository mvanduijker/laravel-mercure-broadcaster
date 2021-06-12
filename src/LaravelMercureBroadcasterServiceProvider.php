<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster;

use Duijker\LaravelMercureBroadcaster\Broadcasting\Broadcasters\MercureBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\Jwt\StaticTokenProvider;

class LaravelMercureBroadcasterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app
            ->make(BroadcastManager::class)
            ->extend('mercure', function ($app, array $config) {
                $jwtConfiguration = Configuration::forSymmetricSigner(
                    new Sha256(),
                    InMemory::plainText($config['secret'])
                );

                $token = $jwtConfiguration->builder()
                    ->withClaim('mercure', ['publish' => ['*']])
                    ->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey())
                    ->toString();

                return new MercureBroadcaster(
                    new Hub(
                        $config['url'],
                        new StaticTokenProvider($token)
                    )
                );
            });
    }

    public function register()
    {
    }
}
