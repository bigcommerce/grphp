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

namespace Grphp\Authentication;

use Grphp\Client\Config as ClientConfig;

/**
 * Builds Authentication metadata dependent on the authentication config option
 * @package Grphp\Authentication
 */
class Builder
{
    /**
     * @param ClientConfig $config
     * @return mixed
     */
    public static function fromClientConfig(ClientConfig $config)
    {
        $auth = null;
        if (is_string($config->authentication)) {
            switch (strtolower($config->authentication)) {
                case 'basic':
                    $auth = new Basic($config->authenticationOptions);
                    break;
                default:
                    if (class_exists($config->authentication)) {
                        $class = $config->authentication;
                        $auth = new $class($config->authenticationOptions);
                    }
            }
        } elseif (is_object($config->authentication)) {
            return $config->authentication;
        }
        return $auth;
    }
}
