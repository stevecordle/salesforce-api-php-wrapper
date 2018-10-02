<?php

namespace Crunch\Salesforce\Tests;

use Carbon\Carbon;
use Crunch\Salesforce\AccessToken;
use Crunch\Salesforce\AccessTokenGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crunch\Salesforce\AccessTokenGenerator
 */
class AccessTokenGeneratorTest extends TestCase
{

    public function testTokenGetsGeneratedFromJson()
    {
        $jsonToken = json_encode([
            'id' => '',
            'dateIssued' => '',
            'dateExpires' => '',
            'scope' => '',
            'tokenType' => '',
            'refreshToken' => '',
            'signature' => '',
            'accessToken' => '',
            'apiUrl' => '',
        ]);
        $tokenGenerator = new AccessTokenGenerator();
        $token = $tokenGenerator->createFromJson($jsonToken);

        self::assertInstanceOf(AccessToken::class, $token, 'Token generated not an instance of AccessToken');
    }

    public function testTokenGetsGeneratedFromSalesforceResponse()
    {
        $responseData = [
            'id' => '',
            'issued_at' => '',
            'scope' => '',
            'token_type' => '',
            'refresh_token' => '',
            'signature' => '',
            'access_token' => '',
            'instance_url' => '',
        ];
        $tokenGenerator = new AccessTokenGenerator();
        $token = $tokenGenerator->createFromSalesforceResponse($responseData);

        $this->assertInstanceOf(AccessToken::class, $token, 'Token generated not an instance of AccessToken');
    }

    public function testTokenGetsGeneratedFromLimitedSalesforceResponse()
    {
        $responseData = [
            'id' => '',
            'issued_at' => '',
            'access_token' => '',
            'instance_url' => '',
        ];
        $tokenGenerator = new AccessTokenGenerator();
        $token = $tokenGenerator->createFromSalesforceResponse($responseData);

        $this->assertInstanceOf(AccessToken::class, $token, 'Token generated not an instance of AccessToken');
    }

    public function testTokenDateGeneratedFromSalesforceResponse()
    {
        $time = time();
        $responseData = [
            'id' => '',
            'issued_at' => $time * 1000, //salesforce uses milliseconds
            'scope' => '',
            'token_type' => '',
            'refresh_token' => '',
            'signature' => '',
            'access_token' => '',
            'instance_url' => '',
        ];
        $tokenGenerator = new AccessTokenGenerator();
        $token = $tokenGenerator->createFromSalesforceResponse($responseData);

        self::assertInstanceOf(Carbon::class, $token->getDateIssued(), 'Token issued date not a carbon instance');
        self::assertEquals($time, $token->getDateIssued()->timestamp, 'Token issue timestamp doesnt match');
        self::assertEquals($time + (60*55), $token->getDateExpires()->timestamp, 'Token expiry time not 55 minutes after creation');
    }

    /** @test */
    public function token_date_generated_from_stored_json()
    {
        $issueDate = '2015-01-02 10:11:12';
        $expiryDate = '2015-01-02 11:11:12';
        $jsonToken = json_encode([
            'id' => '',
            'dateIssued' => $issueDate,
            'dateExpires' => $expiryDate,
            'scope' => '',
            'tokenType' => '',
            'refreshToken' => '',
            'signature' => '',
            'accessToken' => '',
            'apiUrl' => '',
        ]);
        $tokenGenerator = new AccessTokenGenerator();
        $token = $tokenGenerator->createFromJson($jsonToken);

        self::assertInstanceOf(Carbon::class, $token->getDateIssued(), 'Token issued date not a carbon instance');
        self::assertEquals($issueDate, $token->getDateIssued()->format('Y-m-d H:i:s'), 'Token issue timestamp doesnt match');
        self::assertEquals($expiryDate, $token->getDateExpires()->format('Y-m-d H:i:s'), 'Token expiry time not 1 hour after creation');
    }
}
