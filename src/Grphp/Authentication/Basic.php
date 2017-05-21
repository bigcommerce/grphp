<?php
namespace Grphp\Authentication;

/**
 * Basic HTTP Auth adapters for gRPC requests
 *
 * @package Grphp\Authentication
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
                $this->getAuthenticationMetadataKey() => [trim("Basic $authString")],
            ];
        }
    }

    /**
     * @return string
     */
    private function getAuthenticationMetadataKey()
    {
        return array_key_exists('metadata_key', $this->options) ?
            $this->options['metadata_key']
            : 'authorization';
    }
}
