<?php
namespace Grphp\Serializers\Errors;

/**
 * Provides serialization for json metadata trailers
 * @package Grphp\Serializers\Errors
 */
class Json extends Base
{
    /**
     * @param string $trailer
     * @return array
     */
    public function deserialize($trailer)
    {
        return json_decode($trailer, true);
    }
}
