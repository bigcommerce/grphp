<?php
namespace Grphp\Authorization;

use Grphp\Client\Config as ClientConfig;

/**
 * Builds authorization metadata dependent on the authorization
 * @package Grphp\Authorization
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
        if (is_string($config->authorization)) {
            switch (strtolower($config->authorization)) {
                case 'basic':
                    $auth = new Basic($config->authorizationOptions);
                    break;
                default:
                    if (class_exists($config->authorization)) {
                        $auth = new $config->authorization($config->authorizationOptions);
                    }
            }
        } elseif (is_object($config->authorization)) {
            return $config->authorization;
        }
        return $auth;
    }

}
