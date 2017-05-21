<?php
namespace Grphp\Client;

/**
 * Configuration object for Grphp Client
 * @package Grphp\Client
 */
class Config
{
    /** @var string $hostname */
    public $hostname = '';
    /** @var string $authorization */
    public $authorization;
    /** @var array $authorizationOptions */
    public $authorizationOptions = [];
    /** @var string $errorSerializer */
    public $errorSerializer;
    /** @var array $errorSerializerOptions */
    public $errorSerializerOptions = [];

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->hostname = array_key_exists('hostname', $options) ? $options['hostname'] : '';
        $this->authorization = array_key_exists('authorization', $options) ? $options['authorization'] : null;
        $this->authorizationOptions = array_key_exists('authorization_options', $options) ? $options['authorization_options'] : [];
        $this->errorSerializer = array_key_exists('error_serializer', $options) ? $options['error_serializer'] : \Grphp\Serializers\Errors\Json::class;
        $this->errorSerializerOptions = array_key_exists('error_serializer_options', $options) ? $options['error_serializer_options'] : [];
    }
}
