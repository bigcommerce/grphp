<?php
namespace Grphp\Serializers\Errors;

class Bigcommerce extends Base
{
    public function deserialize($trailer)
    {
        $header = new \Bigcommerce\Rpc\Core\ErrorHeader();
        $header->mergeFromString($trailer);
        return $header;
    }
}
