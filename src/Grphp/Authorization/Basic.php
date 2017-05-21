<?php
namespace Grphp\Authorization;

class Basic extends Base
{
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
