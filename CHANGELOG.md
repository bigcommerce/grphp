Changelog for grphp.

### Pending Release

### 3.1.1

* Make Grphp\Client#getClient protected instead of private

### 3.1.0

* Add the ability to configure the content-type header for requests using the H2Proxy strategy

### 3.0.1

* Fix bug with empty metadata in interceptors.

### 3.0.0

* Add PHP8 in composer.json
* Update phpunit ^9
* Migrate to CircleCI

### 2.1.0

* Originally, this library relied solely upon the deadline gRPC feature, i.e. that the service will terminate request
  when the deadline is reached. With this release, client will also terminate the connection after the specified 
  amount of time if the service wasn't able to respond in time and did not terminate the request for some reason.

### 2.0.2

* Fix service name deriving from a client class name in Request

### 2.0.1

* Ensure that no null byte or other unprintable characters are included into the exception message as a result of 
  failed gRPC response with a valid gRPC message.

### 2.0.0

* This release contains improved exception messages, that include the name of the service and a method that was
  invoked but failed.
* Breaking change: removal of the `Grphp\Client\Error::ERROR_METADATA_KEY` constant.

### 1.0.0

* This release contains an improvement to reporting an error status from gRPC stack by propagating it as part of the 
  Status attached to the RequestException.
* A breaking change to the config to require a scheme for a given base URI is introduced in order to fix certain 
  situations where proxied request is sent to the service without a scheme causing request parsing failures.

### 0.5.6

* updated version of google/protobuf library
* minor bug fixes

### 0.5.5

* Include connection error messages in exceptions

### 0.5.4

* Removes the default value for proxy configuration as this option should be disabled by default.

### 0.5.3

* Adds support for explicit proxy configuration when using the H2 strategy.

### 0.5.2

* Reduce dependency on php timer

### 0.5.1

* Suppress "expect: 100-continue" header on outbound cURL requests for the H2Proxy strategy

### 0.5.0

* Introduce new strategy patterns for communicating out to services, allowing either gRPC or H2Proxy for the client
* Adds H2Proxy strategy for utilizing nghttpx to proxy H1 requests into H2 gRPC requests without need of the PHP C
extension
* Adds a new Error\Status class for representing gRPC error statuses
* Adds a new client request object for encapsulating contextual information about the outgoing request
* Adds new Header and HeaderCollection classes for representing HTTP headers both outbound and inbound  

### 0.4.0

* Update to gRPC 1.9.x
* Get off BC gRPC fork now that root SSL memory leak issue is fixed

### 0.3.9

* Ensure that client stubs are not instantiated on construction, but rather lazily loaded on first service call

### 0.3.2

* Ensure LinkerD interceptor pulls from SERVER and REQUEST
* Ensure LinkerD interceptor handles transformation of context keys

### 0.3.1

* Fix timer interceptor to properly report in ms
* Add more unit tests

### 0.3.0

* Improved interceptor config support, useDefaultInterceptors config option

### 0.2.1

* Allow client stub to be accessible to interceptors
* Add isSuccess to \Grphp\Client\Response

### 0.2.0

* Add LinkerD context propagation interceptor
* Add interceptor options
* Set l5d + timer interceptors to be default

### 0.1.1

* Fix channel issue for gRPC 1.3.2

### 0.1.0

* Rename instrumentors to interceptors

### 0.0.3

* Rollback to gRPC 1.3.2 until https://github.com/grpc/grpc/issues/11711 is fixed
