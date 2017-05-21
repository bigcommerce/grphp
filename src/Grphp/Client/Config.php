<?php
namespace Grphp\Client;

class Config
{
    public $hostname = '';
    public $authorization;
    public $authorizationOptions = [];
    public $errorSerializer;
    public $errorSerializerOptions = [];

    public function __construct($options = [])
    {
        $this->hostname = array_key_exists('hostname', $options) ? $options['hostname'] : '';
        $this->authorization = array_key_exists('authorization', $options) ? $options['authorization'] : null;
        $this->authorizationOptions = array_key_exists('authorization_options', $options) ? $options['authorization_options'] : [];
        $this->errorSerializer = array_key_exists('error_serializer', $options) ? $options['error_serializer'] : \Grphp\Serializers\Errors\Json::class;
        $this->errorSerializerOptions = array_key_exists('error_serializer_options', $options) ? $options['error_serializer_options'] : [];
    }
}
