<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Broadcasting;

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
        $this->name = $name;
        $this->private = $private;
    }

    public function __toString()
    {
        return $this->name;
    }
}
