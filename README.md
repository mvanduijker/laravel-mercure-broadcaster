# Laravel Mercure Broadcaster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mvanduijker/laravel-mercure-broadcaster.svg?style=flat-square)](https://packagist.org/packages/mvanduijker/laravel-mercure-broadcaster)
![Build status](https://github.com/mvanduijker/laravel-mercure-broadcaster/workflows/Run%20tests/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/mvanduijker/laravel-mercure-broadcaster.svg?style=flat-square)](https://packagist.org/packages/mvanduijker/laravel-mercure-broadcaster)


Laravel broadcaster for [Mercure](https://github.com/dunglas/mercure) for doing Server Sent Events in a breeze.

## Installation

Make sure you have installed [Mercure](https://github.com/dunglas/mercure) and have it running. Check their docs how to 
do it. (It's pretty easy)

Configure laravel to use the Mercure broadcaster by editing `config/broadcasting.php` for example:

```php
<?php

return [

    'default' => env('BROADCAST_DRIVER', 'mercure'),

    'connections' => [

        // ...

        'mercure' => [
            'driver' => 'mercure',
            'url' => env('MERCURE_URL', 'http://localhost:3000/.well-known/mercure'),
            'secret' => env('MERCURE_SECRET', 'aVerySecretKey'),
        ],

    ],

];
```

## Usage

Add an event which implements ShouldBroadcast interface like in https://laravel.com/docs/master/broadcasting#defining-broadcast-events

```php
<?php

namespace App\Events;

use Duijker\LaravelMercureBroadcaster\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewsItemCreated implements ShouldBroadcast
{
    /**
     * @var NewsItem
     */
    public $newsItem;

    public function __construct(NewsItem $newsItem)
    {
        $this->newsItem = $newsItem;
    }

    public function broadcastOn()
    {
        return new Channel('http://example/news-items');
    }
}
```

In your frontend do something like:

```javascript
var es = new EventSource('http://localhost:3000/.well-known/mercure?topic=' + encodeURIComponent('http://example/news-items'));
es.addEventListener('message', (messageEvent) => {
    var eventData = JSON.parse(messageEvent.data);
    console.log(eventData);
});
```


Private channels go a bit differently than with broadcasting through sockets. Private channels are baked in Mercure and
are secured with a jwt token.

First create a http middleware, so we can generate the Mercure authentication cookie with the token. 
Don't forget to add the middleware to your route!

Example:

```php
<?php 

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class MercureBroadcasterAuthorizationCookie
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if (!method_exists($response, 'withCookie')) {
            return $response;
        }

        return $response->withCookie($this->createCookie($request->user(), $request->secure()));
    }

    private function createCookie($user, bool $secure)
    {
        // Add topic(s) this user has access to
        // This can also be URI Templates (to match several topics), or * (to match all topics)
        $subscriptions = [
            "http://example/user/{$user->id}/direct-messages",
        ];

        $jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(config('broadcasting.connections.mercure.secret'))
        );

        $token = $jwtConfiguration->builder()
            ->withClaim('mercure', ['subscribe' => $subscriptions])
            ->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey())
            ->toString();

        return Cookie::make(
            'mercureAuthorization',
            $token,
            15,
            '/.well-known/mercure', // or which path you have mercure running
            parse_url(config('app.url'), PHP_URL_HOST),
            $secure,
            true
        );
    }
}
```

Because Laravel encrypts and decrypts cookies by default, don't forget to add an [exception](https://laravel.com/docs/9.x/responses#cookies-and-encryption) for the `mercureAuthorization` cookie in `App\Http\Middleware\EncryptCookies`.

Example event:

```php
<?php

namespace App\Events;

use Duijker\LaravelMercureBroadcaster\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DirectMessageCreated implements ShouldBroadcast
{
    /**
     * @var DirectMessage
     */
    public $directMessage;

    public function __construct(DirectMessage $directMessage)
    {
        $this->directMessage = $directMessage;
    }

    public function broadcastOn()
    {
        return new Channel(
            "http://example/user/{$this->directMessage->user_id}/direct-messages", 
            true
        );
    }
}
```

Example Frontend:

```javascript
var es = new EventSource('http://localhost:3000/.well-known/mercure?topic=' + encodeURIComponent('http://example/user/1/direct-messages'), { withCredentials: true });
es.addEventListener('message', (messageEvent) => {
    var eventData = JSON.parse(messageEvent.data);
    console.log(eventData);
});
```

### Advanced usage

If you want to generate your own JWT, you can do it by overriding the `mvanduijker.mercure_broadcaster.publisher_jwt` service. 
You want to do this if you want to have custom claims, using other signing algorithms, etc. It expects a string back containing the JWT.
Example how the default JWT is generated: https://github.com/mvanduijker/laravel-mercure-broadcaster/blob/master/src/LaravelMercureBroadcasterServiceProvider.php#L32

Make sure you also make the changes in the cookie middleware.

### Further reading

Make sure you read the documentation of Mercure and how to run it securely (behind https).

* [Mercure documentation](https://github.com/dunglas/mercure)
* [Symfony integration document](https://symfony.com/doc/current/mercure.html)



### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [Mark van Duijker](https://github.com/mvanduijker)
- [Kévin Dunglas](https://github.com/dunglas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
