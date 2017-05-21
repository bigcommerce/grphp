<?php
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
