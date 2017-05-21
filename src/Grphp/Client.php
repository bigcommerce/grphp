<?php
namespace Grphp;

use Grpc\ChannelCredentials;
use Grphp\Client\Config;

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
     * Issue the call to the server
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
        \PHP_Timer::start();
        $metadata = array_merge($this->buildAuthenticationMetadata(), $metadata);
        list($resp, $status) = $this->client->$method($request, $metadata, $options)->wait();

        $time = \PHP_Timer::stop();

        if (!is_null($resp)) {
            return new Client\Response($resp, $status, $time);
        } else {
            throw new Client\Error($this->config, $status, $time);
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
