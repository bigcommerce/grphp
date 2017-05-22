<?php
namespace Grphp\Client;

use Grphp\Serializers\Errors\Base as BaseSerializer;

/**
 * Abstract a gRPC error, providing status codes, timings, and trailing metadata error
 * deserialization.
 *
 * @package Grphp\Client
 */
class Error extends \Exception
{
    /** @const string */
    const ERROR_METADATA_KEY = 'error-internal-bin';

    /** @var \stdClass $status */
    protected $status;
    /** @var Config $config */
    private $config;

    /**
     * @param \Grphp\Client\Config $config
     * @param \stdClass $status
     * @param float $elapsed
     */
    public function __construct(Config $config, $status)
    {
        $this->status = $status;
        $this->config = $config;
        parent::__construct("Error: $status->details", $status->code);
    }

    /**
     * @return mixed
     */
    public function getTrailer()
    {
        $trailer = null;
        $err = $this->getTrailingMetadataError();
        if ($err) {
            $serializer = $this->getErrorSerializer();
            if ($serializer) {
                $trailer = $serializer->deserialize($err);
            }
        }
        return $trailer;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return null|string
     */
    private function getTrailingMetadataError()
    {
        return $this->status
            && $this->status->metadata
            && array_key_exists(self::ERROR_METADATA_KEY, $this->status->metadata)
            && is_array($this->status->metadata[self::ERROR_METADATA_KEY])
                ? $this->status->metadata[self::ERROR_METADATA_KEY][0]
                : null;
    }

    /**
     * @return \Grphp\Serializers\Errors\Base
     */
    private function getErrorSerializer()
    {
        $class = $this->config->errorSerializer;
        if (is_object($class) && is_a($class, BaseSerializer::class)) {
            return $class;
        } elseif (is_string($class) && class_exists($class)) {
            return new $class($this->config->errorSerializerOptions);
        } else {
            return null;
        }
    }
}
