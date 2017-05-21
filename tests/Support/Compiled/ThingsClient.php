<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Grphp\Test {

    class ThingsClient extends \Grpc\BaseStub {

        /**
         * @param string $hostname hostname
         * @param array $opts channel options
         * @param \Grpc\Channel $channel (optional) re-use channel object
         */
        public function __construct($hostname, $opts, $channel = null) {
            parent::__construct($hostname, $opts, $channel);
        }

        /**
         * @param \Grphp\Test\GetThingReq $argument input argument
         * @param array $metadata metadata
         * @param array $options call options
         */
        public function GetThing(\Grphp\Test\GetThingReq $argument,
                                 $metadata = [], $options = []) {
            return $this->_simpleRequest('/grphp.test.Things/GetThing',
                $argument,
                ['\Grphp\Test\GetThingResp', 'decode'],
                $metadata, $options);
        }

    }

}
