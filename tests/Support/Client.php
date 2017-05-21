<?php
namespace Grphp\Test {
    use Google\Protobuf\Internal\GPBUtil;

    class Thing extends \Google\Protobuf\Internal\Message
    {
        private $id = 0;

        private $name = '';

        public function __construct() {
            \GPBMetadata\Grphp\Test\Things::initOnce();
            parent::__construct();
        }

        public function getId()
        {
            return $this->id;
        }

        public function setId($var)
        {
            GPBUtil::checkInt64($var);
            $this->id = $var;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setName($var)
        {
            GPBUtil::checkString($var, True);
            $this->name = $var;
        }
    }

    class ThingsClient extends \Grpc\BaseStub
    {
        public function GetThing(\Grphp\Test\GetThingReq $argument, $metadata = [], $options = [])
        {
            return $this->_simpleRequest('/grphp.test.Things/GetThing',
                $argument,
                ['\Grphp\Test\GetThingResp', 'decode'],
                $metadata, $options);
        }
    }

    class GetThingReq extends \Google\Protobuf\Internal\Message
    {

        public function __construct()
        {
            \GPBMetadata\Grphp\Test\Things::initOnce();
            parent::__construct();
        }

    }

    class GetThingResp extends \Google\Protobuf\Internal\Message
    {
        public function __construct()
        {
            \GPBMetadata\Grphp\Test\Things::initOnce();
            parent::__construct();
        }

        public function getThing()
        {
            return $this->thing;
        }

        public function setThing(&$var)
        {
            GPBUtil::checkMessage($var, \Grphp\Test\Thing::class);
            $this->block_template = $var;
        }
    }
}

namespace GPBMetadata\Grphp\Test {

    class Things
    {
        public static $is_initialized = false;

        public static function initOnce()
        {
            $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

            if (static::$is_initialized == true) {
                return;
            }
            //$pool->internalAddGeneratedFile(hex2bin());
            static::$is_initialized = true;
        }
    }
}
