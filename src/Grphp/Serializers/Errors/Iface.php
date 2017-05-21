<?php
namespace Grphp\Serializers\Errors;

/**
 * Interface for Grphp serializer classes
 *
 * @package Grphp\Serializers\Errors
 */
interface Iface
{
    /**
     * @param string $trailer
     * @return mixed
     */
    public function deserialize($trailer);
}
