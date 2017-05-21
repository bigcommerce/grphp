<?php
namespace Grphp;

use Grphp\Client\Config;

class Client
{
    protected $client;
    protected $config;

    /**
     * @param string $clientClass
     * @param Client\Config|null $config
     */
    public function __construct($clientClass, Config $config = null)
    {
        $this->config = $config;
        $this->client = new $clientClass($config->hostname, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
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
        $metadata = array_merge($this->buildAuthorizationMetadata(), $metadata);
        list($resp, $status) = $this->client->$method($request, $metadata, $options)->wait();

        $time = \PHP_Timer::stop();

        if (!is_null($resp)) {
            return new Client\Response($resp, $status, $time);
        } else {
            throw new Client\Error($this->config, $status, $time);
        }
    }

    public function buildAuthorizationMetadata()
    {
        $authorization = Authorization\Builder::fromClientConfig($this->config);
        if ($authorization) {
            return $authorization->getMetadata();
        } else {
            return [];
        }
    }
}
