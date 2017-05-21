<?php
namespace Grphp\Serializers\Errors;

/**
 * Base class for Error serializer classes.
 *
 * @package Grphp\Serializers\Errors
 */
abstract class Base implements Iface
{
    /** @var array $options */
    protected $options = [];

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
    }
}
