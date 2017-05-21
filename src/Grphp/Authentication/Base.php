<?php
namespace Grphp\Authentication;

/**
 * Class Base
 * @package Grphp\Authentication
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
