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
declare(strict_types=1);

namespace Grphp\Client\Strategy\Envoy;

use Grphp\Client\HeaderCollection;

/**
 * Execute egress requests to Envoy via cURL
 */
class RequestExecutor
{
    const GRPC_BINARY_ENCODED_METADATA_POSTFIX = '-bin';
    const GRPC_ENCODING = 'identity';
    const GRPC_STATUS_HEADER = 'grpc-status';
    const GRPC_STATUS_OK = '0';
    const GRPHP_USER_AGENT = 'grphp/1.0.0';
    const PACK_ARGS = '\0';
    const PACK_FORMAT = 'cN';
    const PACK_START = 5;

    private const UNCOMPRESSED_EMPTY_GRPC_MESSAGE = "\x00\x00\x00\x00\x00";
    private const COMPRESSED_EMPTY_GRPC_MESSAGE = "\x01\x00\x00\x00\x00";
    private const MILLISECONDS_IN_SECOND = 1000;

    /**
     * curl automatically sets "expect: 100-continue" header, if either
     * - the request is a PUT, or
     * - the request is a POST and the data size is larger than 1024 bytes
     *
     * the header is not always correctly handled by servers,
     * especially http2 based; curl won't send it, if the following header is set
     */
    const EXPECT_CONTINUE_DISABLING_HEADER = 'expect:';

    /**
     * @param Request $request
     * @return Response
     * @throws RequestException
     */
    public function send(Request $request): Response
    {
        $payload = $this->buildPayload($request);
        $responseHeaders = new HeaderCollection();

        $ch = curl_init($request->getUrl());
        curl_setopt_array($ch, $this->getCurlOptions($request, $payload));
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($ch, $header) use ($responseHeaders) {
                $vs = explode(':', $header, 2);
                if (!empty($vs) && isset($vs[1])) {
                    $k = $vs[0];
                    $v = $vs[1];
                    if (strpos(strtolower($k), static::GRPC_BINARY_ENCODED_METADATA_POSTFIX) > 0) {
                        // need to base64 decode binary encoded metadata here, since gRPC normally does this for us
                        $v = base64_decode($v);
                    } else {
                        $v = trim($v);// otherwise, we need to trim the output
                    }
                    $responseHeaders->add($k, $v);
                }
                return strlen($header);
            }
        );

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RequestException(curl_error($ch), $responseHeaders);
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        return $this->handleResponse($responseHeaders, substr($response, $headerSize));
    }

    /**
     * @param HeaderCollection $responseHeaders
     * @param string $body
     * @return Response
     * @throws RequestException
     */
    private function handleResponse(HeaderCollection $responseHeaders, string $body): Response
    {
        $header = $responseHeaders->get(static::GRPC_STATUS_HEADER);
        if (!$header) {
            $message = $body;
            if ($this->isEmptyMessage($message)) {
                $message = "Missing grpc-status header";
            }

            throw new RequestException($message, $responseHeaders);
        }

        if ($header->getFirstValue() != static::GRPC_STATUS_OK) {
            throw new RequestException("gRPC status: {$header->getFirstValue()}", $responseHeaders);
        }

        if (!empty($body)) {
            $body = substr($body, static::PACK_START); // strip off pack
        }

        return new Response($body, $responseHeaders);
    }

    /**
     * @param string $body
     * @return bool
     */
    private function isEmptyMessage(string $body): bool
    {
        return !$body ||
            $body === static::COMPRESSED_EMPTY_GRPC_MESSAGE ||
            $body === static::UNCOMPRESSED_EMPTY_GRPC_MESSAGE;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function buildPayload(Request $request): string
    {
        $message = $request->getMessage();
        return pack(static::PACK_FORMAT, static::PACK_ARGS, strlen($message)) . $message;
    }

    /**
     * @param Request $request
     * @param string $payload
     * @return array
     */
    private function getCurlOptions(Request $request, string $payload): array
    {
        $headers = $request->getHeaders()->compress();
        $headers[] = self::EXPECT_CONTINUE_DISABLING_HEADER;
        $curlOptions = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HEADER => true,
            CURLOPT_USERAGENT => static::GRPHP_USER_AGENT,
            CURLOPT_ENCODING => static::GRPC_ENCODING
        ];
        if ($request->getTimeout() !== null) {
            $curlOptions[CURLOPT_TIMEOUT_MS] = round($request->getTimeout() * self::MILLISECONDS_IN_SECOND);
        }
        return $curlOptions;
    }
}
