# grphp - gRPC PHP Framework

grphp is a PHP framework that wraps the [gRPC PHP library](https://github.com/grpc/grpc/tree/master/src/php) to
provide a more streamlined integration into PHP applications.

It provides an abstracted client for gRPC services, along with other tools to help get gRPC services in PHP
up fast and efficiently at scale. Some of its features include:

* Robust client error handling and metadata transport abilities
* Server authentication strategy support, with basic auth with multiple key support built in
* Error data serialization in output metadata to allow fine-grained error handling in the transport while still 
preserving gRPC BadStatus codes
* Client execution timings in responses

grphp currently has active support for gRPC 1.3.x.

## Installation

```json
{
  "require": {
    "bigcommerce/grphp": "dev-master"
  }
}
```

## Client

```php
$config = new Grphp\Client\Config([
    'hostname' => 'IP_OF_SERVER:PORT',
]);
$client = new Grphp\Client(Things\ThingsClient::class, $config);

$request = new Things\GetThingReq();
$request->setId(1234);

$resp = $client->call($request, 'GetThing');
$thing = $resp->getResponse(); // Things\Thing
echo $thing->id; // 1234
echo $resp->getElapsed(); // 1.03ms
echo $resp->getStatusDetails(); // OK
``` 

## Authentication

Authentication is done via adapters, which are specified in the config. You can either pass
in:

* The string "basic" for basic HTTP auth
* A string class name
* An instantiated object that extends `Grphp\Authentication\Base

### Basic Authentication

grphp supports basic auth for requests that is sent through the metadata of the request. 

```php
$config = new Grphp\Client\Config([
    'hostname' => 'IP_OF_SERVER:PORT',
    'authentication' => 'basic',
    'authenticationOptions' => [
        'username' => 'foo',
        'password' => 'bar', // optional
    ]
]);
```

## Roadmap

* Add instrumentor support for client requests (to enable statsd, zipkin, etc)
* Add TLS configuration support

## License

Copyright 2017, Bigcommerce Inc.
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

* Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above
copyright notice, this list of conditions and the following disclaimer
in the documentation and/or other materials provided with the
distribution.
* Neither the name of BigCommerce Inc. nor the names of its
contributors may be used to endorse or promote products derived from
this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
