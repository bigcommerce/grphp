<?php
namespace Grphp\Client;

/**
 * Abstracts a gRPC response to provide accessor information into metadata, status codes,
 * trailing output metadata, and more.
 *
 * @package Grphp\Client
 */
class Response
{
    /** @var \Google\Protobuf\Internal\Message $response */
    protected $response;
    /** @var \stdClass $status */
    protected $status;
    /** @var float $elapsed */
    protected $elapsed = 0.0;

    /**
     * @param \Google\Protobuf\Internal\Message $response
     * @param \stdClass $status
     * @param float $elapsed
     */
    public function __construct($response, $status, $elapsed)
    {
        $this->response = $response;
        $this->status = $status;
        $this->elapsed = $elapsed;
    }

    /**
     * @return \Google\Protobuf\Internal\Message
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status->code;
    }

    /**
     * @return string
     */
    public function getStatusDetails()
    {
        return $this->status->details;
    }

    /**
     * @return array
     */
    public function getStatusMetadata()
    {
        return $this->status->metadata;
    }

    /**
     * @return \stdClass
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return float
     */
    public function getElapsed()
    {
        return $this->elapsed;
    }

    /**
     * @return float
     */
    public function getInternalExecutionTime()
    {
        return array_key_exists('timer', $this->status->metadata)
          && count($this->status->metadata['timer']) > 0
            ? floatval($this->status->metadata['timer'][0])
            : 0.0;
    }
}
