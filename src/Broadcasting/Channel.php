<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Broadcasting;

/**
 * @deprecated please use Illuminate\Broadcasting\Channel classes
 */
class Channel
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $private;

    public function __construct(string $name, bool $private = false)
    {
        trigger_deprecation('mvanduijker/laravel-mercure-broadcaster', '3,8.0', sprintf('Class %s is deprecated, use %s or for private channels use %s', __CLASS__, \Illuminate\Broadcasting\Channel::class, \Illuminate\Broadcasting\PrivateChannel::class));

        $this->name = $name;
        $this->private = $private;
    }

    public function __toString()
    {
        return $this->name;
    }
}
