<?php
namespace Grphp\Test;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    protected $clientConfig;
    protected $client;

    protected function buildClient(array $options = [])
    {
        $options = array_merge([
            'hostname' => '0.0.0.0:9000',
        ], $options);

        $this->clientConfig = new \Grphp\Client\Config($options);
        $this->client = new \Grphp\Client(\Grphp\Test\ThingsClient::class, $this->clientConfig);
    }
}
