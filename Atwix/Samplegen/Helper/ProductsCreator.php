<?php

namespace Atwix\Samplegen\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\CatalogWidget\Model\Rule\Condition\ProductFactory;
use Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Registry;
use \Atwix\Samplegen\Console\Command\GenerateProductsCommand;
use Magento\Catalog\Model\Product\Type as Type;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Store\StoreManager;

// TODO refactor for ability to use abstract generator class

class ProductsCreator extends \Magento\Framework\App\Helper\AbstractHelper
{
    const NAMES_PREFIX = 'smlpgn_';
    const DEFAULT_STORE_ID = 0;
    const DEFAULT_CATEGORY_ID = 2;
    const DEFAULT_PRODUCT_PRICE = '100';
    const DEFAULT_PRODUCT_WEIGHT = '2';
    const DEFAULT_PRODUCT_QTY = '50';
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

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $availableCategories;


    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $parameters
    )
    {
        $this->parameters = $parameters;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->titlesGenerator = $objectManager->create('Atwix\Samplegen\Helper\TitlesGenerator');
        parent::__construct($context);
    }

    public function launch()
    {
        $this->registry->register('isSecureArea', true);
        $adminAppState = $this->objectManager->get('Magento\Framework\App\State');
        $adminAppState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMIN);

        if (false == $this->parameters[GenerateProductsCommand::INPUT_KEY_REMOVE]) {
            return $this->createProducts();
        } else {
            return $this->removeGeneratedItems();
        }
    }

    public function createProducts()
    {
        $this->createSimpleProduct();
    }

    public function createSimpleProduct()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');

        $websitesList = $this->storeManager->getWebsites(true);
        $websitesIds = array_keys($websitesList);

        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setStoreId(self::DEFAULT_STORE_ID)
            ->setAttributeSetId($product->getDefaultAttributeSetId())
            ->setName(self::NAMES_PREFIX . $this->titlesGenerator->generateProductTitle())
            ->setPrice(self::DEFAULT_PRODUCT_PRICE)
            ->setWeight(self::DEFAULT_PRODUCT_WEIGHT)
            ->setSku(uniqid())
            ->setWebsiteIds($websitesIds)
            ->setQty(self::DEFAULT_PRODUCT_QTY);

        $productCategories = [];

        /** @var \Magento\Catalog\Model\Product $categoryProduct */
        $categoryProduct = $this->getProductCategory();

        /* Get random category for product */
        if ($categoryProduct->getId() != self::DEFAULT_CATEGORY_ID) {
            $productCategories[] = $categoryProduct->getId();
        }

        /* Also assign current product to the default category */
        $productCategories[] = self::DEFAULT_CATEGORY_ID;

        $product->setCategoryIds($productCategories)
            ->save();
    }

    public function createConfigurableProduct()
    {

    }

    protected function getProductCategory()
    {
        if (NULL == $this->availableCategories) {
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoriesCollection */
            $categoriesCollection = $this->categoryFactory->create()->getCollection();
            $categoriesCollection->addAttributeToFilter('entity_id', ['gt' => '0'])
                ->addIsActiveFilter();
            $this->availableCategories = $categoriesCollection->getItems();
        }

        if (count($this->availableCategories) > 0) {
            return $this->availableCategories[array_rand($this->availableCategories)];
        } else {
            throw new \Exception("There are no categories available in the store");
        }
    }

    protected function removeGeneratedItems()
    {
//        /** @var \Magento\Catalog\Model\Category $category */
//        $product = $this->objectManager->create('Magento\Catalog\Model\Product'); // FIXME change to get
//
//        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoriesCollection */
//        $categoriesCollection = $category->getCollection();
//        $generatedCategories = $categoriesCollection->addAttributeToFilter('name',
//            ['like' => self::CAT_NAME_PREFIX . '%']);
//
//        /** @var \Magento\Catalog\Model\Category $generatedCategory */
//        $generatedCategories = $generatedCategories->getItems();
//        foreach ($generatedCategories as $generatedCategory) {
//            $generatedCategory->delete();
//        }
//
//        return true;
    }
}