<?php
/**
 * Copyright (c) 2017-present, BigCommerce Pty. Ltd. All rights reserved
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
declare(strict_types = 1);

namespace Grphp\Client;

use Grphp\Client\Strategy\Grpc\Config as GrpcConfig;
use Grphp\Client\Strategy\Grpc\Strategy as GrpcStrategy;
use Grphp\Client\Strategy\StrategyInterface;
use Grphp\Serializers\Errors\Json as JsonSerializer;

/**
 * Configuration object for Grphp Client
 * @package Grphp\Client
 */
class Config
{
    /** @var string $hostname */
    public string $hostname;
    /** @var string $authentication */
    public $authentication;
    /** @var array $authenticationOptions */
    public $authenticationOptions;
    /** @var string $errorSerializer */
    public $errorSerializer;
    /** @var array $errorSerializerOptions */
    public $errorSerializerOptions;
    /** @var string $errorMetadataKey */
    public $errorMetadataKey;
    /** @var array $interceptorOptions */
    public $interceptorOptions;
    /** @var bool $useDefaultInterceptors */
    public $useDefaultInterceptors;
    /** @var bool $retriesEnabled Whether to enable retries on the client */
    public bool $retriesEnabled;
    /** @var array $retriesStatusCodes The gRPC status codes that will be retried. Defaults to UNAVAILABLE. */
    public array $retriesStatusCodes;
    /** @var int $retriesMaxAttempts How many retries will be done */
    public int $retriesMaxAttempts;
    /** @var string $retriesInitialBackoff The initial backoff period (string, e.g. '0.1s') for each retry attempt */
    public string $retriesInitialBackoff;
    /** @var float $retriesBackoffMultiplier The multiplier for the backoff interval for successive retry attempts */
    public float $retriesBackoffMultiplier;
    /** @var string $retriesMaxBackoff The maximum amount of seconds to backoff by per retry */
    public string $retriesMaxBackoff;
    /** @var array $channelArguments An array of channel options to pass when constructing the client */
    public array $channelArguments;
    /** @var StrategyInterface $strategy */
    private $strategy;

    /** @var string */
    private const ERROR_METADATA_KEY = 'error-internal-bin';
    /** @var string[] The default status codes to retry */
    private const DEFAULT_RETRIES_STATUS_CODES = ['UNAVAILABLE'];
    /** @var int The default maximum number of retries to attempt */
    private const DEFAULT_RETRIES_MAX_ATTEMPTS = 3;
    /** @var string The default backoff interval for retries (multiplied by the backoff multiplier for successive retries) */
    private const DEFAULT_RETRIES_INITIAL_BACKOFF = '0.1s';
    /** @var float Default period to ease the backoff on retries, multiplied by the initial backoff */
    private const DEFAULT_RETRIES_BACKOFF_MULTIPLIER = 2.0;
    /** @var string The default maximum backoff period for retries */
    private const DEFAULT_RETRIES_MAX_BACKOFF = '0.5s';

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->hostname = (string)($options['hostname'] ?? '');
        $this->authentication = $options['authentication'] ?? null;
        $this->authenticationOptions = $options['authentication_options'] ?? [];
        $this->errorSerializer = $options['error_serializer'] ?? JsonSerializer::class;
        $this->errorSerializerOptions = $options['error_serializer_options'] ?? [];
        $this->errorMetadataKey = $options['error_metadata_key'] ?? self::ERROR_METADATA_KEY;
        $this->interceptorOptions = $options['interceptor_options'] ?? [];
        $this->useDefaultInterceptors = $options['use_default_interceptors'] ?? true;
        $this->channelArguments = array_key_exists('channel_arguments', $options) && is_array($options['channel_arguments']) ? $options['channel_arguments'] : [];
        $this->strategy = $options['strategy'] ?? new GrpcStrategy(new GrpcConfig());
        $this->retriesEnabled = (bool)($options['retries_enabled'] ?? true);
        $this->retriesStatusCodes = $options['retries_status_codes'] ?? static::DEFAULT_RETRIES_STATUS_CODES;
        $this->retriesMaxAttempts = (int)($options['retries_max_attempts'] ?? static::DEFAULT_RETRIES_MAX_ATTEMPTS);
        $this->retriesInitialBackoff = (string)($options['retries_initial_backoff'] ?? static::DEFAULT_RETRIES_INITIAL_BACKOFF);
        $this->retriesBackoffMultiplier = (float)($options['retries_backoff_multiplier'] ?? static::DEFAULT_RETRIES_BACKOFF_MULTIPLIER);
        $this->retriesMaxBackoff = (string)($options['retries_max_backoff'] ?? static::DEFAULT_RETRIES_MAX_BACKOFF);
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategy(): StrategyInterface
    {
        return $this->strategy;
    }

    /**
     * @param mixed $strategy
     * @return $this
     */
    public function setStrategy($strategy): Config
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMetadataKey(): string
    {
        return $this->errorMetadataKey;
    }
}
