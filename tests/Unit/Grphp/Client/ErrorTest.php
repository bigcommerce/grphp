<?php
namespace Grphp\Client;

use \Grphp\Test\BaseTest;

final class ErrorTest extends BaseTest
{
    /** @var Error */
    protected $error;
    /** @var \stdClass */
    protected $status;
    /** @var float */
    protected $elapsed;

    public function setUp()
    {
        $this->buildClient([
            'error_serializer' => \Grphp\Serializers\Errors\Json::class,
        ]);
        $this->elapsed = rand(0.0, 20.0);
        $status = new \stdClass();
        $status->code = 0;
        $status->details = 'OK';
        $status->metadata = [
            Error::ERROR_METADATA_KEY => ['{"message": "Test"}'],
        ];
        $this->status = $status;
        $this->error = new Error($this->clientConfig, $status, $this->elapsed);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(\Grphp\Client\Error::class, $this->error);
        $this->assertEquals("Error: {$this->status->details} - {$this->elapsed}ms", $this->error->getMessage());
        $this->assertInstanceOf(\Grphp\Client\Config::class, $this->error->getConfig());
        $this->assertEquals($this->status->code, $this->error->getCode());
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(\Grphp\Client\Config::class, $this->error->getConfig());
        $this->assertEquals($this->clientConfig, $this->error->getConfig());
    }

    public function testGetTrailer()
    {
        $this->assertEquals(['message' => 'Test'], $this->error->getTrailer());
    }
}
