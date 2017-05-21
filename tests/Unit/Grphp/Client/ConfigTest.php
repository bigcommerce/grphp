<?php
namespace Grphp\Client;

use Grphp\Test\BaseTest;

final class ConfigTest extends BaseTest
{
    public function testConstructor()
    {
        $this->assertTrue(true);
    }

    public function testDefaults()
    {
        $config = new Config();
        $this->assertEquals('', $config->hostname);
        $this->assertEquals(null, $config->authentication);
        $this->assertEquals([], $config->authenticationOptions);
        $this->assertEquals(\Grphp\Serializers\Errors\Json::class, $config->errorSerializer);
        $this->assertEquals([], $config->errorSerializerOptions);
    }
}
