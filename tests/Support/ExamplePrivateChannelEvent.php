<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Tests\Support;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ExamplePrivateChannelEvent implements ShouldBroadcastNow
{
    public $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function broadcastOn()
    {
        return new PrivateChannel(
            "http://example/private-channel-event",
        );
    }
}
