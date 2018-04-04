<?php
/**
 * Copyright (c) 2017-present, BigCommerce Pty. Ltd. All rights reserved
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
declare(strict_types = 1);

namespace Grphp\Client;

/**
 * Represents an HTTP header for requests/responses
 */
class Header
{
    /** @var string */
    private $name;
    /** @var string[] */
    private $values = [];

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = strtolower(trim($name));
    }

    /**
     * Return the name of this header
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Add a value for this header
     *
     * @param string $v
     * @return $this
     */
    public function addValue(string $v): Header
    {
        $this->values[] = $v;
        return $this;
    }

    /**
     * Return all values of this header
     *
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return string
     */
    public function getValuesAsString(): string
    {
        return trim(implode(',', $this->getValues()));
    }

    /**
     * Return the first value of this header, or a default if not set
     *
     * @param string $default
     * @return string
     */
    public function getFirstValue(string $default = ''): string
    {
        return $this->values[0] ?? $default;
    }
}
