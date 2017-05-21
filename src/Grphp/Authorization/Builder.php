<?php
/**
 * Created by PhpStorm.
 * User: shaun.mccormick
 * Date: 5/19/17
 * Time: 10:27 AM
 */

namespace Grphp\Authorization;

use Grphp\Client\Config as ClientConfig;

class Builder
{
    /**
     * @param ClientConfig $config
     * @return Basic|null
     */
    public static function fromClientConfig(ClientConfig $config)
    {
        $auth = null;
        switch (strtolower($config->authorization)) {
            case 'basic':
                $auth = new Basic($config->authorizationOptions);
        }
        return $auth;
    }

}
