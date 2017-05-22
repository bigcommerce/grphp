<?php
namespace Grphp\Client;

use \Grphp\Test\BaseTest;

final class ResponseTest extends BaseTest
{
    /** @var \Grphp\Test\GetThingResp */
    protected $resp;
    /** @var \Grphp\Client\Response */
    protected $response;
    /** @var \stdClass */
    protected $statusObj;
    /** @var float */
    protected $elapsed;

    public function setUp()
    {
        $thing = new \Grphp\Test\Thing();
        $thing->setId(1234);
        $thing->setName('Test');
        $this->resp = new \Grphp\Test\GetThingResp();
        $this->resp->setThing($thing);

        $this->statusObj = new \stdClass();
        $this->statusObj->code = 0;
        $this->statusObj->details = 'OK';
        $this->statusObj->metadata = [
            'timer' => [rand(0.0, 200.0)],
        ];

        $this->elapsed = rand(0.0, 200.0);
        $this->response = new Response($this->resp, $this->statusObj);
        $this->response->setElapsed($this->elapsed);
    }

    public function testGetResponse()
    {
        $this->assertEquals($this->resp, $this->response->getResponse());
    }

    public function testGetStatusCode()
    {
        $this->assertEquals($this->statusObj->code, $this->response->getStatusCode());
    }

    public function testGetStatusDetails()
    {
        $this->assertEquals($this->statusObj->details, $this->response->getStatusDetails());
    }

    public function testGetMetadata()
    {
        $this->assertEquals($this->statusObj->metadata, $this->response->getMetadata());
    }

    public function testGetStatus()
    {
        $this->assertEquals($this->statusObj, $this->response->getStatus());
    }

    public function testGetElapsed()
    {
        $this->assertEquals($this->elapsed, $this->response->getElapsed());
    }

    public function testGetInternalExecutionTime()
    {
        $this->assertEquals($this->statusObj->metadata['timer'][0], $this->response->getInternalExecutionTime());
    }
}
