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

You'll need to make sure you fit [the requirements for the grpc/grpc PHP library](https://github.com/grpc/grpc/tree/master/src/php#environment),
which does involve installing the gRPC PHP extension.

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
echo $resp->getStatusCode(); // 0 (these are gRPC status codes)
echo $resp->getStatusDetails(); // OK
``` 

## Authentication

Authentication is done via adapters, which are specified in the config. You can either pass in:

* The string "basic" for basic HTTP auth
* A string class name for an existing class
* An instantiated object that extends `Grphp\Authentication\Base`

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

## Instrumentation

grphp supports an instrumentation registry. To add an instrumentor, simply call `addInstrumentor` on the client:

```php
$client->addInstrumentor(new \Grphp\Instrumentation\Timer());
```

### Custom Instrumentors

grphp comes with a base Instrumentation class that can be extended to provide your own custom instrumentors. This is an
example instrumentor that adds a "X-Foo" header with a customizable value to all metadata:

```php
<?php
use Grphp\Client\Response;
use Grphp\Instrumentation\Base as BaseInstrumentor;

class FooHeader extends BaseInstrumentor
{
    /**
     * @param callable $callback
     * @return Response
     */
    public function measure(callable $callback)
    {
        /** @var Response $response */
        $response = $callback();
        $response->setMetadata([
            'X-Foo' => $this->options['foo_value'],
        ]);
        return $response;
    }
}
```

You'll note that you have to make sure to execute the callback that is called.

Then you add it as normal:

```php
$i = new FooHeader(['foo_value' => 'bar']);
$client->addInstrumentor($i);
```

Instrumentors run in the order that they are added, wrapping each as they go.

## Error Handling

gRPC prefers handling errors through status (BadStatus) codes; however, these do not return much information as to 
field specific errors, application codes, or debug information. grphp provides a way to read data from the response 
metadata, which is stored in the `error-internal-bin` key (configurable through the `error_metadata_key` configuration 
option).

Assuming we have a service that has a method that appends that data, you can access it like so:

```php
try {
    $resp = $client->call($request, 'GetErroringMethod');
    
} catch (\Grphp\Client\Error $e) {
    $trailer = $e->getTrailer();
    var_dump($trailer); // ['message' => 'Foo']
}
```

By default the deserializer for the data is JSON; it's fairly simple to create your own, such as one that has the error 
header serialized as a binary protobuf. From there, you can set it simply:

```php
class MyProtoSerializer extends Grphp\Serializers\Errors\Base
{
    public function deserialize($trailer)
    {
        $header = new \My\Proto\ErrorHeader();
        $header->mergeFromString($trailer);
        return $header;
    }
}

$config = new Grphp\Client\Config([
    'hostname' => 'IP_OF_SERVER:PORT',
    'error_serializer' => new MyProtoSerializer(),
]);
```

The serializer can be passed as a string name of the class or the instance of the class. If you pass the string name,
you can pass in an associative array of `error_serializer_options` to the config to provide options for your serializer.

## Roadmap

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
