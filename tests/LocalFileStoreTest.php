<?php

use \Mockery as m;

class LocalFileStoreTest extends TestCase
{

    /** @test */
    public function file_store_can_be_instantiated()
    {
        $tokenGenerator = m::mock('Crunch\Salesforce\AccessTokenGenerator');
        $config = m::mock('Crunch\Salesforce\TokenStore\LocalFileConfigInterface');
        $config->shouldReceive('getFilePath')->once()->andReturn('/foo');
        $fileStore = new \Crunch\Salesforce\TokenStore\LocalFile($tokenGenerator, $config);

        $this->assertInstanceOf(\Crunch\Salesforce\TokenStore\LocalFile::class, $fileStore);
    }


}