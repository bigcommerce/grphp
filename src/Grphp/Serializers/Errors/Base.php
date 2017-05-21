<?php
namespace Grphp\Serializers\Errors;

abstract class Base implements Iface
{
    protected $options = [];

    public function __construct($options = [])
    {
        $this->options = $options;
    }
}
