<?php
namespace Grphp\Client;

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
    /** @var float $elapsed */
    protected $elapsed;
    /** @var Config $config */
    private $config;

    /**
     * @param \Grphp\Client\Config $config
     * @param \stdClass $status
     * @param float $elapsed
     */
    public function __construct(Config $config, $status, $elapsed)
    {
        $this->status = $status;
        $this->elapsed = $elapsed;
        $this->config = $config;
        parent::__construct("Error: $status->details - ${elapsed}ms", $status->code);
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
            $trailer = $serializer->deserialize($err);
        }
        return $trailer;
    }

    /**
     * @return null|string
     */
    private function getTrailingMetadataError()
    {
        return $this->status
            && $this->status->metadata
            && array_key_exists(self::ERROR_METADATA_KEY, $this->status->metadata)
            && count($this->status->metadata[self::ERROR_METADATA_KEY]) > 0
                ? trim($this->status->metadata[self::ERROR_METADATA_KEY][0])
                : null;
    }

    /**
     * @return \Grphp\Serializers\Errors\Base
     */
    private function getErrorSerializer()
    {
        return new $this->config->errorSerializer($this->config->errorSerializerOptions);
    }
}
