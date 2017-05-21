<?php
namespace Grphp\Serializers\Errors;

class Json extends Base
{
    public function deserialize($trailer)
    {
        return json_decode($trailer, true);
    }
}
