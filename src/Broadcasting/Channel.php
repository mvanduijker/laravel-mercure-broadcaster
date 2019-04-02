<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Broadcasting;

class Channel
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $targets;

    public function __construct(string $name, array $targets = [])
    {
        $this->name = $name;
        $this->targets = $targets;
    }

    public function __toString()
    {
        return $this->name;
    }
}
