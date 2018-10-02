<?php

namespace Crunch\Salesforce\Tests;

use Crunch\Salesforce\AccessTokenGenerator;
use Crunch\Salesforce\TokenStore\LocalFile;
use Crunch\Salesforce\TokenStore\LocalFileConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Crunch\Salesforce\TokenStore\LocalFile
 */
class LocalFileTest extends TestCase
{

    public function testCanBeInstantiated()
    {
        /** @var AccessTokenGenerator $tokenGenerator */
        $tokenGenerator = $this->getMockBuilder(AccessTokenGenerator::class)->disableOriginalConstructor()->getMock();

        /** @var LocalFileConfigInterface|MockObject $config */
        $config = $this->getMockBuilder(LocalFileConfigInterface::class)->getMockForAbstractClass();
        $config->expects(self::once())->method('getFilePath')->willReturn('/foo');
        $fileStore = new LocalFile($tokenGenerator, $config);

        self::assertInstanceOf(LocalFile::class, $fileStore);
    }
}
