<?php
namespace Grphp;

use Grpc\ChannelCredentials;
use Grphp\Client\Config;
use Grphp\Instrumentation\Base as BaseInstrumentor;
use Grphp\Instrumentation\Timer;

/**
 * Layers over gRPC client communication to provide extra response, header, and timing
 * information.
 *
 * @package Grphp
 */
class Client
{
    /** @var \Grpc\BaseStub $client */
    protected $client;
    /** @var Config $config */
    protected $config;
    /** @var array<BaseInstrumentor> $instrumentors */
    protected $instrumentors = [];

    /**
     * @param string $clientClass
     * @param Client\Config|null $config
     */
    public function __construct($clientClass, Config $config)
    {
        $this->config = $config;
        $this->client = new $clientClass($config->hostname, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }

    /**
     * Issue the call to the server, wrapping with the given instrumentors
     *
     * @param \Google\Protobuf\Internal\Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @return Client\Response
     * @throws Client\Error
     */
    public function call($request, $method, array $metadata = [], array $options = [])
    {
        $metadata = array_merge($this->buildAuthenticationMetadata(), $metadata);
        $instrumentors = $this->instrumentors;
        return $this->instrument($instrumentors, $request, $method, $metadata, $options, function() use (&$instrumentors, &$request, &$method, &$metadata, &$options)
        {
            list($resp, $status) = $this->client->$method($request, $metadata, $options)->wait();
            if (!is_null($resp)) {
                $response = new Client\Response($resp, $status);
            } else {
                throw new Client\Error($this->config, $status);
            }
            return $response;
        });
    }

    /**
     * Instrument the call with the registered instrumentors for the client
     *
     * @param array $instrumentors
     * @param \Google\Protobuf\Internal\Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @param callable $callback
     * @return mixed
     */
    public function instrument(array $instrumentors, $request, $method, $metadata, $options, callable $callback)
    {
        $i = array_shift($instrumentors);
        if ($i) {
            return $i->measure(function () use (&$instrumentors, &$method, &$request, &$metadata, &$options, &$callback) {
                if (count($instrumentors) > 0) {
                    return $this->instrument($instrumentors, $request, $method, $metadata, $options, $callback);
                } else {
                    return $callback();
                }
            });
        } else {
            return $callback();
        }
    }

    /**
     * Add an instrumentor to the client instrumentation registry
     *
     * @param BaseInstrumentor $instrumentor
     * @throws \InvalidArgumentException if the instrumentor does not extend \Grphp\Instrumentation\Base
     */
    public function addInstrumentor(BaseInstrumentor $instrumentor)
    {
        if (is_a($instrumentor, BaseInstrumentor::class)) {
            $this->instrumentors[] = $instrumentor;
        } else {
            throw new \InvalidArgumentException("Instrumentor does not extend \\Grphp\\Instrumentation\\Base");
        }
    }

    /**
     * @return array
     */
    public function buildAuthenticationMetadata()
    {
        $authentication = Authentication\Builder::fromClientConfig($this->config);
        if ($authentication) {
            return $authentication->getMetadata();
        } else {
            return [];
        }
    }
}
