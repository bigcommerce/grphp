Changelog for grphp.

h3. 1.0.0

* Support for PHP7.0+ only
* Move authentication into interceptors to allow control of authentication timings
* Simplify configuration and add type checking
* Add channel configuration support
* Add interceptor registry for future insertion order support

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
