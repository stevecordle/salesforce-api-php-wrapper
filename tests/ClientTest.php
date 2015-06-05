<?php

use \Mockery as m;

class ClientTest extends TestCase {



    /** @test */
    public function client_can_be_instantiated()
    {
        $guzzle = m::mock('\GuzzleHttp\Client');

        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);

        $this->assertInstanceOf(\Crunch\Salesforce\Client::class, $sfClient);
    }

    /** @test */
    public function client_will_get_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['foo' => 'bar']));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('get')->with(stringContainsInOrder('Test', $recordId, 'field1,field2') , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->getRecord('Test', $recordId, ['field1', 'field2']);

        $this->assertEquals(['foo' => 'bar'], $data);
    }

    /** @test */
    public function client_can_create_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(['id' => $recordId]));


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('post')->with(containsString('Test') , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->createRecord('Test', ['field1', 'field2']);

        $this->assertEquals($recordId, $data);
    }

    /** @test */
    public function client_can_update_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');

        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('patch')->with(stringContainsInOrder('Test', $recordId) , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->updateRecord('Test', $recordId, ['field1', 'field2']);

        $this->assertTrue($data);
    }

    /** @test */
    public function client_can_delete_record()
    {
        $recordId = 'abc' . rand(1000, 9999999);

        $response = m::mock('Psr\Http\Message\ResponseInterface');


        $guzzle = m::mock('\GuzzleHttp\Client');
        //Make sure the url contains the passed in data
        $guzzle->shouldReceive('delete')->with(stringContainsInOrder('Test', $recordId) , \Mockery::type('array'))->once()->andReturn($response);


        $sfClient = new \Crunch\Salesforce\Client($this->getClientConfigMock(), $guzzle);
        $sfClient->setAccessToken($this->getAccessTokenMock());


        $data = $sfClient->deleteRecord('Test', $recordId);

        $this->assertTrue($data);
    }


    /**
     * Mock the client config interface
     * @return m\MockInterface
     */
    private function getClientConfigMock()
    {
        $config = m::mock('Crunch\Salesforce\ClientConfigInterface');
        $config->shouldReceive('getLoginUrl')->once()->andReturn('http://login.example.com');
        $config->shouldReceive('getClientId')->once()->andReturn('client_id');
        $config->shouldReceive('getClientSecret')->once()->andReturn('client_secret');
        return $config;
    }

    private function getAccessTokenMock()
    {
        $accessToken = m::mock('Crunch\Salesforce\AccessToken');
        $accessToken->shouldReceive('getApiUrl')->andReturn('http://api.example.com');
        $accessToken->shouldReceive('getAccessToken')->andReturn('123456789abcdefghijk');
        return $accessToken;
    }
}