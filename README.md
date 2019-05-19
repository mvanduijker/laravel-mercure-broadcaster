# Laravel Mercure Broadcaster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mvanduijker/laravel-mercure-broadcaster.svg?style=flat-square)](https://packagist.org/packages/mvanduijker/laravel-mercure-broadcaster)
[![Build Status](https://img.shields.io/travis/mvanduijker/laravel-mercure-broadcaster/master.svg?style=flat-square)](https://travis-ci.org/mvanduijker/laravel-mercure-broadcaster)
[![Total Downloads](https://img.shields.io/packagist/dt/mvanduijker/laravel-mercure-broadcaster.svg?style=flat-square)](https://packagist.org/packages/mvanduijker/laravel-mercure-broadcaster)


Laravel broadcaster for [Mercure](https://github.com/dunglas/mercure) for doing Server Sent Events in a breeze.

## Installation

Make sure you have installed [Mercure](https://github.com/dunglas/mercure) and have it running. Check their docs how to 
do it. (It's pretty easy)

Configure laravel to use the mercure broadcaster by editing `config/broadcasting.php` for example:

```php
<?php

return [

    'default' => env('BROADCAST_DRIVER', 'mercure'),

    'connections' => [

        // ...

        'mercure' => [
            'driver' => 'mercure',
            'url' => env('MERCURE_URL', 'http://localhost:3000/hub'),
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
var es = new EventSource('http://localhost:3000/hub?topic=' + encodeURIComponent('http://example/news-items'));
es.addEventListener('message', (messageEvent) => {
    var eventData = JSON.parse(messageEvent.data);
    console.log(eventData);
});
```


Private channels go a bit differently then with broadcasting through sockets. Private channels are baked in Mercure and
are secured with a jwt token.

First create a http middleware so we can generate the mercure authentication cookie with the token. 
Don't forget to add the middleware to your route!

Example:

```php
<?php 

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

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
        // Add audience(s) this user has access to
        $subscriptions = [
            "http://example/user/{$user->id}"
        ];

        $token = (new Builder())
            ->setExpiration(time() + (60 * 15))
            ->set('mercure', ['subscribe' => $subscriptions])
            ->sign(new Sha256(), config('broadcasting.connections.mercure.secret'))
            ->getToken();

        return Cookie::make(
            'mercureAuthorization',
            (string) $token,
            15,
            '/hub', // or which path you have mercure running
            parse_url(config('app.url'), PHP_URL_HOST),
            $secure,
            true
        );
    }
}
```

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
            ["http://example/user/{$this->directMessage->user_id}"]
        );
    }
}
```

Example Frontend:

```javascript
var es = new EventSource('http://localhost:3000/hub?topic=' + encodeURIComponent('http://example/user/1/direct-messages'), { withCredentials: true });
es.addEventListener('message', (messageEvent) => {
    var eventData = JSON.parse(messageEvent.data);
    console.log(eventData);
});
```


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
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
