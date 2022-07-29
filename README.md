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
* H2Proxy via nghttpx support that allows gRPC-based communication without the gRPC C libraries

grphp currently has active support for gRPC 1.9.0, and requires PHP 7.4+ to run.

## Installation

```bash
composer require bigcommerce/grphp
```

You'll need to make sure you fit 
[the requirements for the grpc/grpc PHP library](https://github.com/grpc/grpc/tree/master/src/php#environment),
which does involve installing the gRPC PHP extension (unless you are using the H2Proxy strategy).

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

## Strategy

grphp comes with the ability to utilize injectable strategies for how it communicates outward. Currently, there are two
strategies that come packaged with grphp:

* *Grpc* - This strategy class will utilize the core gRPC PHP libraries to communicate outbound to services
* *H2Proxy* - This strategy is set to call out to an [nghttpx](https://nghttp2.org/) proxy to communicate via HTTP/1.1,
  which is then upgraded to an HTTP/2 connection, and transformed into a gRPC request.
  
### H2Proxy Strategy

The H2Proxy strategy pairs with a [nghttpx](https://nghttp2.org/) service and sends HTTP/1.1 requests that are upgraded
to HTTP/2 and gRPC. It does this by sending the binary encoded protobuf across the wire with the `Upgrade: h2c` and 
`Connection: Upgrade` headers, which nghttpx uses to upgrade the connection into a proper gRPC request.

This is useful if you do not want to utilize the gRPC PHP C extension but still gain the benefit of the protobuf
contracts. If you do not have the gRPC PHP C extension installed, grphp will automatically switch you to the H2Proxy
strategy.

You can use and configure the proxy strategy like so, assuming we have a nghttpx service running at the address 0.0.0.0 
on port 3000:

```php
$proxyConfig = new Grphp\Client\Strategy\H2Proxy\Config('http://0.0.0.0:3000', 15);
$proxyStrategyFactory = new Grphp\Client\Strategy\H2Proxy\StrategyFactory($proxyConfig);
$config = new Grphp\Client\Config([
    'strategy' => $proxyStrategyFactory->build(),
]);
```

This sets the proxy client to also utilize a timeout of 15 seconds. This setup is configurable per-client, so you can
adjust these settings - and the strategy - on a service-by-service basis.

### Envoy Strategy

The Envoy strategy uses [Envoy](https://www.envoyproxy.io/) as an HTTP/1.1 bridge for gRPC egress traffic. It 
automatically serializes messages and buffers requests to handle the response trailers. More can be read about the
[Envoy bridge here](https://www.envoyproxy.io/docs/envoy/latest/configuration/http/http_filters/grpc_http1_bridge_filter).

```php
// Connect to Envoy at 127.0.0.1:19000
$envoyConfig = new Grphp\Client\Strategy\Envoy\Config('127.0.0.1', 19000, 2);
$envoyStrategyFactory = new Grphp\Client\Strategy\Envoy\StrategyFactory($envoyConfig);
$config = new Grphp\Client\Config([
    'strategy' => $envoyStrategyFactory->build(),
]);
```

This sets the proxy client to also utilize a timeout of 2 seconds. This setup is configurable per-client, so you can
adjust these settings - and the strategy - on a service-by-service basis.

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
* Experimental gRPC server support via sidecar proxy through FastCGI

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
