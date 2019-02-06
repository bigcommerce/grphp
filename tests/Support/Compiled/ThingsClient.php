<?php
/**
 * Copyright (c) 2017-present, BigCommerce Pty. Ltd. All rights reserved
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Grphp\Test;

class ThingsClient extends \Grpc\BaseStub
{
    public function getExpectedResponseMessages()
    {
        return [
            'getThing' => '\Grphp\Test\GetThingResp',
        ];
    }

    protected $channel;

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null)
    {
        parent::__construct($hostname, $opts, $channel);
        $this->channel = new \Grpc\Channel($hostname, $opts);
    }

    /**
     * @param \Grphp\Test\GetThingReq $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return StubbedCall
     */
    public function GetThing(
        \Grphp\Test\GetThingReq $argument,
        $metadata = [],
        $options = []
    ) {
        $thing = new Thing();
        $thing->setId($argument->getId());
        $thing->setName('Foo');
        $resp = new GetThingResp();
        $resp->setThing($thing);
        return new StubbedCall(
            $resp,
            $this->channel,
            '/grphp.test.Things/GetThing',
            ['\Grphp\Test\GetThingResp', 'decode'],
            $options
        );
    }
}
