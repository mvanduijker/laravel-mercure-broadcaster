<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Tests\Support;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ExampleChannelEvent implements ShouldBroadcastNow
{
    public $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function broadcastOn()
    {
        return new Channel(
            "http://example/channel-event",
        );
    }
}
