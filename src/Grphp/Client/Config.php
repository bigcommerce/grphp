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
    /** @var string $authentication */
    public $authentication;
    /** @var array $authenticationOptions */
    public $authenticationOptions = [];
    /** @var string $errorSerializer */
    public $errorSerializer;
    /** @var array $errorSerializerOptions */
    public $errorSerializerOptions = [];
    /** @var string $errorMetadataKey */
    public $errorMetadataKey = 'error-internal-bin';

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->hostname = array_key_exists('hostname', $options) ? $options['hostname'] : '';
        $this->authentication = array_key_exists('authentication', $options) ? $options['authentication'] : null;
        $this->authenticationOptions = array_key_exists('authentication_options', $options) ? $options['authentication_options'] : [];
        $this->errorSerializer = array_key_exists('error_serializer', $options) ? $options['error_serializer'] : \Grphp\Serializers\Errors\Json::class;
        $this->errorSerializerOptions = array_key_exists('error_serializer_options', $options) ? $options['error_serializer_options'] : [];
        $this->errorMetadataKey = array_key_exists('error_metadata_key', $options) ? $options['error_metadata_key'] : 'error-internal-bin';
    }
}
