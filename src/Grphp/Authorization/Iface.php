<?php
namespace Grphp\Authorization;

/**
 * Interface for authorization adapters for gRPC client requests
 *
 * @package Grphp\Authorization
 */
interface Iface
{
    /**
     * Return authorization metadata that should be injected into the client request metadata
     *
     * @return array
     */
    public function getMetadata();
}
