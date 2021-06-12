<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Tests\Feature;

use Duijker\LaravelMercureBroadcaster\Tests\Support\ExampleEvent;
use Duijker\LaravelMercureBroadcaster\Tests\TestCase;
use Symfony\Component\Process\Process;

class BroadcasterTest extends TestCase
{
    private $mercureDockerContainerId;

    public function test_it_broadcasts()
    {
        event(new ExampleEvent('example data'));

        $this->assertMercureDockerLog(function ($log) {
            return strpos($log, '\"POST /.well-known/mercure HTTP/1.1\" 200 45"') > 0;
        });
    }

    private function assertMercureDockerLog(callable $matcher)
    {
        try {
            $result = retry(3, function () use ($matcher) {
                $output = Process::fromShellCommandline("docker logs {$this->mercureDockerContainerId}")
                    ->mustRun()
                    ->getErrorOutput();

                if (!$matcher($output)) {
                    throw new \Exception;
                };

                return true;
            }, 100);
        } catch (\Exception $exception) {
            $result = false;
        }

        $this->assertTrue($result);
    }

    /** @before */
    public function startMercureServer()
    {
        $command = "docker run -e SERVER_NAME=':80' -e MERCURE_PUBLISHER_JWT_KEY='!ChangeMe!' -e MERCURE_SUBSCRIBER_JWT_KEY='!ChangeMe!' -d -p 3000:80 dunglas/mercure";

        $this->mercureDockerContainerId = Process::fromShellCommandline($command)
            ->mustRun()
            ->getOutput();
    }

    /** @after */
    public function stopMercureServer(): void
    {
        Process::fromShellCommandline("docker kill {$this->mercureDockerContainerId}")
            ->mustRun();
    }
}
