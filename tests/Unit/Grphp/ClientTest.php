<?php
namespace Grphp;

use Grphp\Client;
use Grphp\Test\BaseTest;

final class ClientTest extends BaseTest
{
    public function setUp()
    {
        $this->buildClient();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }
}
