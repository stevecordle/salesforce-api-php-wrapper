<?php 

namespace Crunch\Salesforce\Tests;

use Carbon\Carbon;
use Crunch\Salesforce\AccessToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crunch\Salesforce\AccessToken
 */
class AccessTokenTest extends TestCase
{

    public function testCanCreateAccessToken()
    {
        $issueDate  = Carbon::now();
        $expiryDate = Carbon::now()->addHour();

        $token = new AccessToken(
            'abc123',
            $issueDate,
            $expiryDate,
            ['scopes'],
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        self::assertInstanceOf(AccessToken::class, $token);

        self::assertEquals('access-token', $token->getAccessToken());
        self::assertEquals('refresh-token', $token->getRefreshToken());
        self::assertEquals('http://example.com', $token->getApiUrl());
        self::assertEquals(['scopes'], $token->getScope());

        self::assertInstanceOf(Carbon::class, $token->getDateExpires());
        self::assertInstanceOf(Carbon::class, $token->getDateIssued());
    }

    public function testTokenRefreshIsCorrect()
    {
        $token1 = new AccessToken(
            'abc123',
            Carbon::now(),
            Carbon::now()->addHour(),
            ['scopes'],
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );
        $this->assertFalse($token1->needsRefresh());

        $token2 = new AccessToken(
            'abc123',
            Carbon::now()->subHours(2),
            Carbon::now()->subHour(),
            ['scopes'],
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );
        self::assertTrue($token2->needsRefresh());
    }

    public function testCanConvertToJson()
    {
        $token = new AccessToken(
            'abc123',
            Carbon::now(),
            Carbon::now()->addHour(),
            ['scopes'],
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        self::assertJson($token->toJson());
        self::assertJson((string)$token, 'Token casts to a json string');

    }

    public function testUpdatesCorrectly()
    {
        $token = new AccessToken(
            'abc123',
            Carbon::now(),
            Carbon::now()->addHour(),
            ['scopes'],
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );


        $time = 1429281826;

        $token->updateFromSalesforceRefresh([
            'issued_at' => $time * 1000,
            'signature' => 'new-signature',
            'access_token' => 'new-access-token'
        ]);

        $this->assertEquals('new-access-token', $token->getAccessToken(), 'access token was updated');
        $this->assertInstanceOf(Carbon::class, $token->getDateExpires());
        $this->assertInstanceOf(Carbon::class, $token->getDateIssued());

        $this->assertEquals($time, $token->getDateIssued()->timestamp, 'Timestamp saved and converted correctly');
    }
}
