<?php declare(strict_types=1);

// Copyright 2026 The Stellar PHP SDK Authors. All rights reserved.
// Use of this source code is governed by a license that can be
// found in the LICENSE file.

namespace Soneso\StellarSDKTests\Unit\Util;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ReflectionProperty;
use Soneso\StellarSDK\Soroban\SorobanServer;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Util\HttpClientFactory;

class HttpClientFactoryTest extends TestCase
{
    /**
     * Sends one request through a factory client and returns the request that reached the handler.
     */
    private function sentRequest(array $config = []): RequestInterface
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], '{}')]));
        $stack->push(Middleware::history($history));
        $client = HttpClientFactory::create($config + ['handler' => $stack]);
        $client->get('https://example.com/');
        return $history[0]['request'];
    }

    public function testRequestsAdvertiseGzip(): void
    {
        $request = $this->sentRequest();
        $this->assertSame('gzip', $request->getHeaderLine('Accept-Encoding'));
    }

    public function testCallerConfigTakesPrecedence(): void
    {
        $request = $this->sentRequest(['decode_content' => false]);
        $this->assertSame('', $request->getHeaderLine('Accept-Encoding'));
    }

    public function testBaseUriIsKept(): void
    {
        $client = HttpClientFactory::create(['base_uri' => 'https://example.com/']);
        $this->assertSame('example.com', $client->getConfig('base_uri')->getHost());
        $this->assertSame('gzip', $client->getConfig('decode_content'));
    }

    public function testHorizonClientAdvertisesGzip(): void
    {
        $sdk = new StellarSDK('https://horizon-testnet.stellar.org');
        $this->assertSame('gzip', $sdk->getHttpClient()->getConfig('decode_content'));
    }

    public function testSorobanClientAdvertisesGzip(): void
    {
        $server = new SorobanServer('https://soroban-testnet.stellar.org');
        $property = new ReflectionProperty(SorobanServer::class, 'httpClient');
        $this->assertSame('gzip', $property->getValue($server)->getConfig('decode_content'));
    }
}
