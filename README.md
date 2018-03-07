# grphp - gRPC PHP Framework

[![Build Status](https://travis-ci.com/bigcommerce/grphp.svg?token=D3Cc4LCF9BgpUx4dpPpv&branch=master)](https://travis-ci.com/bigcommerce/grphp)

grphp is a PHP framework that wraps the [gRPC PHP library](https://github.com/grpc/grpc/tree/master/src/php) to
provide a more streamlined integration into PHP applications.

It provides an abstracted client for gRPC services, along with other tools to help get gRPC services in PHP
up fast and efficiently at scale. Some of its features include:

* Robust client error handling and metadata transport abilities
* Server authentication strategy support, with basic auth with multiple key support built in
* Error data serialization in output metadata to allow fine-grained error handling in the transport while still 
preserving gRPC BadStatus codes
* Client execution timings in responses

grphp currently has active support for gRPC 1.9.0, and requires PHP 5.5+ or 7.0+ to run.

## Installation

```bash
composer require bigcommerce/grphp
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
    'authentication_options' => [
        'username' => 'foo',
        'password' => 'bar', // optional
    ]
]);
```

### Custom Client Interceptors

grphp comes with a base Client Interceptor class that can be extended to provide your own custom interceptors. 
This is an example interceptor that adds a "X-Foo" header with a customizable value to all metadata:

```php
<?php
use Grphp\Client\Response;
use Grphp\Client\Interceptors\Base as BaseInterceptor;

class FooHeader extends BaseInterceptor
{
    /**
     * @param callable $callback
     * @return Response
     */
    public function call(callable $callback)
    {
        // set outgoing metadata
        $this->metadata['stuff'] = ['my_thing'];
        // make the outbound call
        $response = $callback();  
        // adjust incoming metadata        
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
$client->addInterceptor($i);
```

Interceptors run in the order that they are added, wrapping each as they go.

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

Copyright (c) 2017-present, BigCommerce Pty. Ltd. All rights reserved 

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated 
documentation files (the "Software"), to deal in the Software without restriction, including without limitation the 
rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit 
persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the 
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE 
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
