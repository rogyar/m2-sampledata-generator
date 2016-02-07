<?php

namespace Atwix\Samplegen\Test\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Atwix\Samplegen\Console\Command\GenerateCategoriesCommand;

class GenerateCategoriesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    public function setUp() {
        $this->mockContext();
    }

    public function testExecute()
    {
        $objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $cagegoriesCreator = $this->getMock('Atwix\Samplegen\Helper\CategoriesCreator', [], [], '', false);
        $objectManager->expects($this->once())->method('create')->willReturn($cagegoriesCreator);
        $cagegoriesCreator->expects($this->once())->method('launch');
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
        $commandTester = new CommandTester(new GenerateCategoriesCommand($objectManagerFactory, $this->context));
        $commandTester->execute([]);
        $expectedMsg = 'Categories were successfully generated' . PHP_EOL;
        $this->assertSame($expectedMsg, $commandTester->getDisplay());
    }

    protected function mockContext()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);
    }
}