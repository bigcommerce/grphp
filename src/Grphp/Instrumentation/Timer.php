<?php
namespace Grphp\Instrumentation;

use Grphp\Client\Response;

class Timer extends Base
{
    /**
     * @param callable $callback
     * @return Response
     */
    public function measure(callable $callback)
    {
        \PHP_Timer::start();
        /** @var Response $response */
        $response = $callback();
        $time = \PHP_Timer::stop();
        $response->setElapsed($time);
        return $response;
    }
}
