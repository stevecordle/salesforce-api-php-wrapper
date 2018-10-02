<?php

namespace Crunch\Salesforce\Tests;

use Crunch\Salesforce\ClientConfig;
use Crunch\Salesforce\ClientConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crunch\Salesforce\ClientConfig
 */
class ClientConfigTest extends TestCase
{

    /** @test */
    public function client_config_can_be_instantiated()
    {
        $sfClientConfig = new ClientConfig('url', 'clientId', 'clientSecret', 'v44.0');

        self::assertInstanceOf(ClientConfig::class, $sfClientConfig);
        self::assertInstanceOf(ClientConfigInterface::class, $sfClientConfig);
    }

    /** @test */
    public function client_config_data_can_be_accessed()
    {
        $sfClientConfig = new ClientConfig('url', 'clientId', 'clientSecret', 'v44.0');

        self::assertEquals('url', $sfClientConfig->getLoginUrl());
        self::assertEquals('clientId', $sfClientConfig->getClientId());
        self::assertEquals('clientSecret', $sfClientConfig->getClientSecret());
    }
}
