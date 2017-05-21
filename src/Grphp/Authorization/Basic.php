<?php
namespace Grphp\Authorization;

/**
 * Basic HTTP Auth adapters for gRPC requests
 *
 * @package Grphp\Authorization
 */
class Basic extends Base
{
    /**
     * @return array
     */
    public function getMetadata()
    {
        if (empty($this->options['password'])) {
            return [];
        } else {
            $username = trim($this->options['username']);
            $password = trim($this->options['password']);
            $username = empty($username) ? '' : "$username:";
            $authString = base64_encode("$username$password");
            return [
                'authorization' => [trim("Basic $authString")],
            ];
        }
    }
}
