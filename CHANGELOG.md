Changelog for grphp.

h3. 0.5.1

* Suppress "expect: 100-continue" header on outbound cURL requests for the H2Proxy strategy

h3. 0.5.0

* Introduce new strategy patterns for communicating out to services, allowing either gRPC or H2Proxy for the client
* Adds H2Proxy strategy for utilizing nghttpx to proxy H1 requests into H2 gRPC requests without need of the PHP C
extension
* Adds a new Error\Status class for representing gRPC error statuses
* Adds a new client request object for encapsulating contextual information about the outgoing request
* Adds new Header and HeaderCollection classes for representing HTTP headers both outbound and inbound  

h3. 0.4.0

* Update to gRPC 1.9.x
* Get off BC gRPC fork now that root SSL memory leak issue is fixed

h3. 0.3.9

* Ensure that client stubs are not instantiated on construction, but rather lazily loaded on first service call

h3. 0.3.2

* Ensure LinkerD interceptor pulls from SERVER and REQUEST
* Ensure LinkerD interceptor handles transformation of context keys

h3. 0.3.1

* Fix timer interceptor to properly report in ms
* Add more unit tests

h3. 0.3.0

* Improved interceptor config support, useDefaultInterceptors config option

h3. 0.2.1

* Allow client stub to be accessible to interceptors
* Add isSuccess to \Grphp\Client\Response

h3. 0.2.0

* Add LinkerD context propagation interceptor
* Add interceptor options
* Set l5d + timer interceptors to be default

h3. 0.1.1

* Fix channel issue for gRPC 1.3.2

h3. 0.1.0

* Rename instrumentors to interceptors

h3. 0.0.3

* Rollback to gRPC 1.3.2 until https://github.com/grpc/grpc/issues/11711 is fixed
