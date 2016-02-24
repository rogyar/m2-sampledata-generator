<?php

namespace Atwix\Samplegen\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\CatalogWidget\Model\Rule\Condition\ProductFactory;
use Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Registry;
use \Atwix\Samplegen\Console\Command\GenerateProductsCommand;

// TODO refactor for ability to use abstract generator class

class ProductsCreator extends \Magento\Framework\App\Helper\AbstractHelper
{
    const NAMES_PREFIX = 'smlpgn_';
    const DEFAULT_STORE_ID = 0;
    const DEFAULT_CATEGORY_ID = 2;
    /**2
     * @var $parameters array
     */
    protected $parameters;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Atwix\Samplegen\Helper\TitlesGenerator
     */
    protected $titlesGenerator;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    protected $availableCategoriesIds;


    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        $parameters
    )
    {
        $this->parameters = $parameters;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->titlesGenerator = $objectManager->create('Atwix\Samplegen\Helper\TitlesGenerator');
        parent::__construct($context);
    }

    public function launch()
    {
        $this->registry->register('isSecureArea', true);

        if (false == $this->parameters[GenerateProductsCommand::INPUT_KEY_REMOVE]) {
            return $this->createProducts();
        } else {
            return $this->removeGeneratedItems();
        }
    }

    public function createProducts()
    {

        //$productModel = $this->objectManager->create('Magento\Catalog\Model\Product'); // FIXME change to get
    }

    public function createSimpleProduct()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $productModel = $this->objectManager->create('Magento\Catalog\Model\Product'); // FIXME change to get


    }

    public function createConfigurableProduct()
    {

    }

    protected function getProductCategory()
    {
        if (NULL == $this->availableCategoriesIds) {
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoriesCollection */
            $categoriesCollection = $this->categoryFactory->create()->getCollection();
            $categoriesCollection->addAttributeToFilter('id', ['gt' => self::DEFAULT_CATEGORY_ID])
                ->addIsActiveFilter();
            if ($categoriesCollection->count() > 0) {
                // Add available categories to the list
            } else {
                // Return default category
            }
        }
    }

    protected function removeGeneratedItems()
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product'); // FIXME change to get

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoriesCollection */
        $categoriesCollection = $category->getCollection();
        $generatedCategories = $categoriesCollection->addAttributeToFilter('name',
            ['like' => self::CAT_NAME_PREFIX . '%']);

        /** @var \Magento\Catalog\Model\Category $generatedCategory */
        $generatedCategories = $generatedCategories->getItems();
        foreach ($generatedCategories as $generatedCategory) {
            $generatedCategory->delete();
        }

        return true;
    }
}