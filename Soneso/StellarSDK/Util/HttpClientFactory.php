<?php declare(strict_types=1);

// Copyright 2026 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.

namespace Soneso\StellarSDK\Util;

use GuzzleHttp\Client;

/**
 * Creates the Guzzle clients the SDK uses by default.
 *
 * Every client asks servers for gzip-compressed responses and decodes them transparently.
 * Horizon serves much smaller list pages this way. Clients supplied by the caller through
 * the SDK's setters or constructor parameters are used as they are.
 */
class HttpClientFactory
{
    /**
     * Creates a Guzzle client that sends "Accept-Encoding: gzip" and decodes the response.
     *
     * Guzzle's default (decode_content = true) sends no Accept-Encoding header when the cURL
     * handler is used, so the server never compresses. Passing the encoding as a string makes
     * both the cURL and the stream handler send the header and decode the body.
     *
     * @param array<string, mixed> $config Guzzle client config; values given here take precedence
     * @return Client The configured client
     */
    public static function create(array $config = []): Client
    {
        return new Client($config + ['decode_content' => 'gzip']);
    }
}
