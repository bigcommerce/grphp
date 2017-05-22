<?php
namespace Grphp\Instrumentation;

abstract class Base
{
    protected $options = [];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    abstract public function measure(callable $callback);
}
