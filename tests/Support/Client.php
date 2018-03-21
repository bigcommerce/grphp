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

require_once __DIR__ . '/Compiled/Things.php';
require_once __DIR__ . '/Compiled/ThingsClient.php';
require_once __DIR__ . '/Compiled/Thing.php';
require_once __DIR__ . '/Compiled/GetThingReq.php';
require_once __DIR__ . '/Compiled/GetThingResp.php';

use Grpc\AbstractCall;
use Grpc\Channel;

class StubbedCall extends AbstractCall
{
    private $response;
    private $options = [];

    public function __construct($response,
                                Channel $channel,
                                $method,
                                $deserialize,
                                array $options = [])
    {
        parent::__construct($channel, $method, $deserialize, $options);
        $this->response = $response;
        $this->options = array_merge($this->options, $options);
    }

    public function wait()
    {
        $code = $this->option('response_code', 0);
        $details = $this->option('response_details', '');
        $metadata = $this->option('response_metadata', []);

        $status = new CallStatus($code, $details, $metadata);

        $this->trailing_metadata = $status->metadata;
        if ($this->option('response_null', false)) {
            return [null, $status];
        } else {
            return [$this->response, $status];
        }
    }

    private function option($k, $default = null)
    {
        return array_key_exists($k, $this->options) ? $this->options[$k] : $default;
    }
}

class CallStatus
{
    public $code = 0;
    public $details = '';
    public $metadata = [];

    public function __construct(int $code = 0, string $details = '', array $metadata = [])
    {
        $this->code = $code;
        $this->details = $details;
        $this->metadata = $metadata;
    }
}
