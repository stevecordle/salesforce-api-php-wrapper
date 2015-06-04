<?php

use \Mockery as m;

class AccessTokenTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function token_gets_generated_from_json()
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
        $tokenGenerator = new \Crunch\Salesforce\AccessTokenGenerator();
        $token = $tokenGenerator->createFromJson($jsonToken);

        $this->assertInstanceOf(\Crunch\Salesforce\AccessToken::class, $token);
    }

    /** @test */
    public function token_gets_generated_from_sf_response()
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
        $tokenGenerator = new \Crunch\Salesforce\AccessTokenGenerator();
        $token = $tokenGenerator->createFromSalesforceResponse($responseData);

        $this->assertInstanceOf(\Crunch\Salesforce\AccessToken::class, $token);
    }

}