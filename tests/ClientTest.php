<?php

namespace Crunch\Salesforce\Tests;

use Crunch\Salesforce\AccessToken;
use Crunch\Salesforce\Client;
use Crunch\Salesforce\ClientConfigInterface;
use Crunch\Salesforce\Exceptions\RequestException;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \Crunch\Salesforce\Client
 */
class ClientTest extends TestCase
{
    /** @test */
    public function testCanBeInstantiated()
    {
        /** @var ClientInterface $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->getMockForAbstractClass();

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);

        self::assertInstanceOf(Client::class, $sfClient);
    }

    /** @test */
    public function testClient_can_be_statically_instantiated()
    {
        $sfClient = Client::create('loginUrl', 'clientId', 'clientSecret', 'v44.0');

        self::assertInstanceOf(Client::class, $sfClient);
    }

    /** @test */
    public function testWillGetRecord()
    {
        $apiVersion = 'v44.0';
        $recordId = 'abc' . rand(1000, 9999999);

        $response = $this->getMockBuilder(ResponseInterface::class)->setMethods(['getBody'])->getMockForAbstractClass();

        $response->expects(self::once())->method('getBody')->willReturn(json_encode(['foo' => 'bar']));

        /** @var ClientInterface|MockObject $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['get'])->getMockForAbstractClass();
        //Make sure the url contains the passed in data
        $guzzle
            ->expects(self::once())
            ->method('get')
            ->with(
                'http://api.example.com/services/data/'.$apiVersion.'/sobjects/Test/'.$recordId.'?fields=field1,field2',
                ['headers' => ['Authorization' => 'Bearer 123456789abcdefghijk']]
            )
            ->willReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock($apiVersion));


        $data = $sfClient->getRecord('Test', $recordId, ['field1', 'field2']);

        self::assertEquals(['foo' => 'bar'], $data);
    }

    public function testCanSearch()
    {
        $query = 'SELECT Name FROM Lead LIMIT 10';
        $apiVersion = 'v44.0';
        $response = $this->getMockBuilder(ResponseInterface::class)->setMethods(['getBody'])->getMockForAbstractClass();
        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn(json_encode(['records' => [], 'done' => true]));

        /** @var ClientInterface|MockObject $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['get'])->getMockForAbstractClass();
        //Make sure the url contains the passed in data
        $guzzle
            ->expects(self::once())
            ->method('get')
            ->with(
                'http://api.example.com/services/data/'.$apiVersion.'/query/?q='.urlencode($query),
                ['headers' => ['Authorization' => 'Bearer 123456789abcdefghijk']]
            )
            ->willReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock($apiVersion));


        $sfClient->search($query);
    }

    /** @test */
    public function testCanCreateRecord()
    {
        $apiVersion = 'v44.0';
        $recordId = 'abc' . rand(1000, 9999999);
        $data = ['field1', 'field2'];

        $response = $this->getMockBuilder(ResponseInterface::class)->setMethods(['getBody'])->getMockForAbstractClass();
        $response->expects(self::once())->method('getBody')->willReturn(json_encode(['id' => $recordId]));


        /** @var ClientInterface|MockObject $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['post'])->getMockForAbstractClass();
        //Make sure the url contains the passed in data
        $guzzle
            ->expects(self::once())
            ->method('post')
            ->with(
                'http://api.example.com/services/data/'.$apiVersion.'/sobjects/Test/',
                [
                    'headers' => [
                        'Authorization' => 'Bearer 123456789abcdefghijk',
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($data)
                ]
            )
            ->willReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock($apiVersion));


        $data = $sfClient->createRecord('Test', $data);

        $this->assertEquals($recordId, $data);
    }

    public function testCanUpdateRecord()
    {
        $apiVersion = 'v44.0';
        $data = ['field1', 'field2'];
        $recordId = 'abc' . rand(1000, 9999999);

        $response = $this->getMockBuilder(ResponseInterface::class)->setMethods(['getBody'])->getMockForAbstractClass();

        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['patch'])->getMockForAbstractClass();
        //Make sure the url contains the passed in data
        $guzzle
            ->expects(self::once())
            ->method('patch')
            ->with(
                'http://api.example.com/services/data/'.$apiVersion.'/sobjects/Test/'.$recordId,
                [
                    'headers' => [
                        'Authorization' => 'Bearer 123456789abcdefghijk',
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($data)
                ]
            )
            ->willReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock($apiVersion));


        $data = $sfClient->updateRecord('Test', $recordId, $data);

        $this->assertTrue($data);
    }

    public function testCanDeleteRecord()
    {
        $apiVersion = 'v44.0';
        $recordId = 'abc' . rand(1000, 9999999);

        $response = $this->getMockBuilder(ResponseInterface::class)->setMethods(['getBody'])->getMockForAbstractClass();

        /** @var ClientInterface|MockObject $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['delete'])->getMockForAbstractClass();
        //Make sure the url contains the passed in data
        $guzzle
            ->expects(self::once())
            ->method('delete')
            ->with(
                'http://api.example.com/services/data/'.$apiVersion.'/sobjects/Test/' . $recordId,
                [
                    'headers' => [
                        'Authorization' => 'Bearer 123456789abcdefghijk',
                    ],
                ]
            )
            ->willReturn($response);


        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock($apiVersion));


        $data = $sfClient->deleteRecord('Test', $recordId);

        $this->assertTrue($data);
    }

    public function testCanCompleteAuthProcess()
    {
        $apiVersion = 'v44.0';
        $recordId = 'abc' . rand(1000, 9999999);

        $response = $this->getMockBuilder(ResponseInterface::class)->setMethods(['getBody'])->getMockForAbstractClass();
        $response->expects(self::once())->method('getBody')->willReturn(json_encode(['id' => $recordId]));

        /** @var ClientInterface|MockObject $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['post'])->getMockForAbstractClass();
        //Make sure the url contains the passed in data
        $guzzle
            ->expects(self::once())
            ->method('post')->with(
                'http://login.example.com/services/oauth2/token',
                [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'code' => 'authCode',
                        'redirect_uri' => 'redirectUrl',
                    ]
                ]
            )
            ->willReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock($apiVersion));

        $sfClient->authorizeConfirm('authCode', 'redirectUrl');
    }

    public function testCanCompleteTokenRefreshProcess()
    {
        $apiVersion = 'v44.0';
        $recordId = 'abc' . rand(1000, 9999999);

        $response = $this->getMockBuilder(ResponseInterface::class)->setMethods(['getBody'])->getMockForAbstractClass();
        $response->expects(self::once())->method('getBody')->willReturn(json_encode(['id' => $recordId]));


        /** @var ClientInterface|MockObject $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['post'])->getMockForAbstractClass();
        //Make sure the url contains the passed in data
        $guzzle
            ->expects(self::once())
            ->method('post')->with(
                'http://login.example.com/services/oauth2/token',
                [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'client_id' => 'client_id',
                        'client_secret' => 'client_secret',
                        'refresh_token' => 'refresh123456789abcdefghijk',
                    ]
                ]
            )
            ->willReturn($response);

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $accessToken = $this->getAccessTokenMock($apiVersion);
        $accessToken->expects(self::once())->method('updateFromSalesforceRefresh');
        $sfClient->setAccessToken($accessToken);

        $sfClient->refreshToken();
    }

    public function tetsClient_can_get_login_url()
    {
        /** @var ClientInterface $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->getMockForAbstractClass();

        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $url = $sfClient->getLoginUrl('redirectUrl');

        $this->assertNotFalse(strpos($url, 'redirectUrl'));
    }

    public function testCan_parse_auth_flow_error()
    {
        self::expectException(RequestException::class);
        self::expectExceptionMessage(''/*'expired authorization code'*/);

        $apiVersion = 'v44.0';

        //Make guzzle throw an exception with the above message
        $guzzleException = $this->getMockBuilder(RequestException::class)->disableOriginalConstructor()->setMethods(['getResponse'])->getMock();

        //Make sure the url contains the passed in data
        /** @var ClientInterface|MockObject $guzzle */
        $guzzle = $this->getMockBuilder(ClientInterface::class)->setMethods(['post'])->getMockForAbstractClass();
        $guzzle
            ->expects(self::once())
            ->method('post')
            ->with('http://login.example.com/services/oauth2/token', ['form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'code' => 'authCode',
                'redirect_uri' => 'redirectUrl',
            ]])
            ->willThrowException($guzzleException);

        //Setup the client
        $sfClient = new Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock($apiVersion));

        //Try the auth flow - this should generate an exception
        $sfClient->authorizeConfirm('authCode', 'redirectUrl');
    }

    /**
     * Mock the client config interface
     * @return ClientConfigInterface|MockObject
     */
    private function getClientConfigMock()
    {
        $config = $this->getMockBuilder(ClientConfigInterface::class)
            ->setMethods(['getLogin', 'getClientId', 'getClientSecret', 'getApiVersion'])
            ->getMockForAbstractClass();

        $config->expects(self::once())->method('getLoginUrl')->willReturn('http://login.example.com');
        $config->expects(self::once())->method('getClientId')->willReturn('client_id');
        $config->expects(self::once())->method('getClientSecret')->willReturn('client_secret');
        $config->expects(self::once())->method('getApiVersion')->willReturn('v44.0');
        return $config;
    }

    /**
     * @param string $apiVersion
     * @return MockObject|AccessToken
     */
    private function getAccessTokenMock(string $apiVersion)
    {
        $accessToken = $this->getMockBuilder(AccessToken::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getApiUrl',
                'getAccessToken',
                'getRefreshToken',
                'getApiVersion',
                'updateFromSalesforceRefresh'
            ])
            ->getMock();

        $accessToken->expects(self::once())->method('getApiUrl')->willReturn('http://api.example.com');
        $accessToken->expects(self::any())->method('getAccessToken')->willReturn('123456789abcdefghijk');
        $accessToken->expects(self::any())->method('getRefreshToken')->willReturn('refresh123456789abcdefghijk');
        $accessToken->expects(self::any())->method('getApiVersion')->willReturn($apiVersion);

        return $accessToken;
    }
}
