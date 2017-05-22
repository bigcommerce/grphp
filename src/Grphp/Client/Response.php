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
    public function __construct($response, $status)
    {
        $this->response = $response;
        $this->status = $status;
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
    public function getMetadata()
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
     * @param float $time
     */
    public function setElapsed($time)
    {
        $this->elapsed = $time;
    }

    /**
     * @param array $newMetadata
     * @param bool $merge
     */
    public function setMetadata(array $newMetadata = [], $merge = true)
    {
        if ($merge) {
            $this->status->metadata = array_merge($this->status->metadata, $newMetadata);
        } else {
            $this->status->metadata = $newMetadata;
        }
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
