<?php 

class AccessTokenTest extends TestCase {

    /** @test */
    public function can_create_access_token()
    {
        $token = new \Crunch\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            'scopes',
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        $this->assertInstanceOf(\Crunch\Salesforce\AccessToken::class, $token);

        $this->assertEquals('access-token', $token->getAccessToken());
        $this->assertEquals('refresh-token', $token->getRefreshToken());
    }

    /** @test */
    public function token_refresh_is_correct()
    {
        $token1 = new \Crunch\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            'scopes',
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );
        $this->assertFalse($token1->needsRefresh());

        $token2 = new \Crunch\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now()->subHours(2),
            \Carbon\Carbon::now()->subHour(),
            'scopes',
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );
        $this->assertTrue($token2->needsRefresh());
    }

    /** @test */
    public function can_convert_to_json()
    {
        $token = new \Crunch\Salesforce\AccessToken(
            'abc123',
            \Carbon\Carbon::now(),
            \Carbon\Carbon::now()->addHour(),
            'scopes',
            'type',
            'refresh-token',
            'signature',
            'access-token',
            'http://example.com'
        );

        $jsonArray = $token->toJson();

        $this->isJson($jsonArray);

    }
}