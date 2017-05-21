<?php
namespace Grphp\Client;

class Error extends \Exception
{
    protected $status;
    protected $elapsed;
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

    private function getTrailingMetadataError()
    {
        return $this->status
            && $this->status->metadata
            && array_key_exists('error-internal-bin', $this->status->metadata)
            && count($this->status->metadata['error-internal-bin']) > 0
                ? trim($this->status->metadata['error-internal-bin'][0])
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
