<?php

namespace Atwix\Samplegen\Test\Helper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class TitlesGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Atwix\Samplegen\Helper\TitlesGenerator */
    protected $namesGenerator;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->namesGenerator = $this->objectManager->getObject('\Atwix\Samplegen\Helper\TitlesGenerator');
    }

    /**
     * Checks that category title generator produces correct words count
     */
    public function testGenerateCategoryTitle()
    {
        $wordsInCategory = 3;

        $categoryName = $this->namesGenerator->generateCategoryTitle();
        $this->assertCount($wordsInCategory, explode(' ', $categoryName),
            "Category name should contain $wordsInCategory words. Current $categoryName");
    }
}