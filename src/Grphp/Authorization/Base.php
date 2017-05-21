<?php
/**
 * Created by PhpStorm.
 * User: shaun.mccormick
 * Date: 5/19/17
 * Time: 10:30 AM
 */

namespace Grphp\Authorization;


abstract class Base implements Iface
{
    protected $options = [];

    public function __construct($options = [])
    {
        $this->options = $options;
    }
}
