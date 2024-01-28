<?php declare(strict_types = 1);

namespace Duijker\LaravelMercureBroadcaster\Tests\Feature;

use Duijker\LaravelMercureBroadcaster\Tests\Support\ExampleEvent;
use Duijker\LaravelMercureBroadcaster\Tests\TestCase;
use Symfony\Component\Process\Process;

class BroadcasterTest extends TestCase
{
    private $mercureDockerContainerId;

    /**
     * @dataProvider supportedMercureVersionsDataProvider
     */
    public function test_it_broadcasts($mercureVersion)
    {
        $this->startMercureServer($mercureVersion);
        event(new ExampleEvent('example data'));

        $this->assertMercureDockerLog(function ($log) {
            return strpos($log, '\"POST /.well-known/mercure HTTP/1.1\" 200 45"') > 0
                || (strpos($log, '"uri": "/.well-known/mercure"') > 0 && strpos($log, '"status": 200') > 0)
                || (strpos($log, '"uri":"/.well-known/mercure"') > 0 && strpos($log, '"status":200') > 0);
        });
    }

    public function supportedMercureVersionsDataProvider()
    {
        yield ['v0.11'];
        yield ['v0.12'];
        yield ['v0.13'];
        yield ['v0.14'];
        yield ['v0.15'];
        yield ['latest'];
    }

    private function assertMercureDockerLog(callable $matcher)
    {
        try {
            $result = retry(3, function () use ($matcher) {
                $output = Process::fromShellCommandline("docker logs {$this->mercureDockerContainerId}")
                    ->mustRun()
                    ->getErrorOutput();

                if (!$matcher($output)) {
                    throw new \Exception($output);
                };

                return true;
            }, 100);
        } catch (\Exception $exception) {
            $result = false;
            dump($exception->getMessage());
        }


        $this->assertTrue($result);
    }

    /** before */
    public function startMercureServer($version)
    {
        $command = "docker run -e SERVER_NAME=':80' -e MERCURE_PUBLISHER_JWT_KEY='bfaf06ec-ac9d-11ed-a49f-6bc3bc0854c9' -e MERCURE_SUBSCRIBER_JWT_KEY='bfaf06ec-ac9d-11ed-a49f-6bc3bc0854c9' -d -p 3000:80 dunglas/mercure:$version";

        $this->mercureDockerContainerId = Process::fromShellCommandline($command)
            ->mustRun()
            ->getOutput();

        sleep(1);
    }

    /** @after */
    public function stopMercureServer(): void
    {
        Process::fromShellCommandline("docker kill {$this->mercureDockerContainerId}")
            ->mustRun();
    }
}
