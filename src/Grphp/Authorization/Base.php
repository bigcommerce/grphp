<?php
namespace Grphp\Authorization;

/**
 * Class Base
 * @package Grphp\Authorization
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
