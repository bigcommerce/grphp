<?php
namespace Grphp\Client;

class Response
{
    protected $response;
    protected $status;
    protected $elapsed = 0;

    public function __construct($response, $status, $elapsed)
    {
        $this->response = $response;
        $this->status = $status;
        $this->elapsed = $elapsed;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getStatusCode()
    {
        return $this->status->code;
    }

    public function getStatusDetails()
    {
        return $this->status->details;
    }

    public function getStatusMetadata()
    {
        return $this->status->metadata;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getElapsed()
    {
        return $this->elapsed;
    }

    public function getInternalExecutionTime()
    {
        return array_key_exists('timer', $this->status->metadata)
          && count($this->status->metadata['timer']) > 0
            ? floatval($this->status->metadata['timer'][0])
            : null;
    }
}
