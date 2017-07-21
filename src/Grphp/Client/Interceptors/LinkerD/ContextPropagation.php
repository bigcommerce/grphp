<?php
namespace Grphp\Client\Interceptors\LinkerD;

use Grphp\Client\Interceptors\Base;

class ContextPropagation extends Base
{
    const METADATA_KEYS = [
        'l5d-dtab',
        'l5d-ctx-dtab',
        'l5d-ctx-deadline',
        'l5d-ctx-trace'
    ];

    /**
     * gRPC requires metadata keys to be as a string. Also, the PHP gRPC library expects them in an array.
     *
     * @param callable $callback
     */
    public function call(callable $callback)
    {
        foreach (self::METADATA_KEYS as $k) {
            if (array_key_exists($k, $_REQUEST)) {
                $this->metadata[$k] = ['' . $_REQUEST[$k]];
            }
        }
        $response = $callback();
        return $response;
    }
}
