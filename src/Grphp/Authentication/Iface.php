<?php
namespace Grphp\Authentication;

/**
 * Interface for Authentication adapters for gRPC client requests
 *
 * @package Grphp\Authentication
 */
interface Iface
{
    /**
     * Return authentication metadata that should be injected into the client request metadata
     *
     * @return array
     */
    public function getMetadata();
}
