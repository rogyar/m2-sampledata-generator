<?php

namespace Atwix\Samplegen\Test\Helper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Atwix\Samplegen\Helper\TitlesGenerator;

class CategoriesCreatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Atwix\Samplegen\Helper\CategoriesCreator */
    protected $categoriesCreator;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Atwix\Samplegen\Helper\TitlesGenerator
     */
    protected $titlesGenerator;



    public function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false, false);
        $this->titlesGenerator = new TitlesGenerator();
        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getRootCategoryId')->willReturn(0);
        $this->objectManagerMock->expects($this->once())->method('get')
            ->with($this->equalTo('Magento\Store\Model\StoreManagerInterface'))
            ->willReturn($storeManagerMock);

        $this->mockContext();
    }

    public function testRemoveGeneratedCategories()
    {
        $categoryMock = $this->_categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $categoryCollectionMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Category\Collection',
            [],
            [],
            '',
            false
        );
        $categoryCollectionMock->expects($this->any())->method('getItems')->willReturn([$categoryMock]);
        $categoryCollectionMock->expects($this->any())->method('addAttributeToFilter')->willReturn($categoryCollectionMock);
        $categoryMock->expects($this->once())->method('getCollection')->willReturn($categoryCollectionMock);
        $categoryMock->expects($this->once())->method('delete')->willReturn(true);
        $this->objectManagerMock->expects($this->any())->method('create')->willReturn($categoryMock);

        $this->categoriesCreator = $this->objectManager->getObject('\Atwix\Samplegen\Helper\CategoriesCreator', [
            'parameters' => ['removeall' => true],
            'context' => $this->context,
            'objectManager' =>  $this->objectManagerMock,
            'registry' => $this->registryMock,
            'titlesGenerator' => $this->titlesGenerator
        ]);

        $this->assertTrue($this->categoriesCreator->launch());
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