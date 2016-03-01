<?php

namespace Atwix\Samplegen\Helper;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\CatalogWidget\Model\Rule\Condition\ProductFactory;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;
use Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Registry;
use \Atwix\Samplegen\Console\Command\GenerateProductsCommand;
use Magento\Catalog\Model\Product\Type as Type;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

// TODO refactor for ability to use abstract generator class
// TODO add a separate atribute instead a name prefix for all items

class ProductsCreator extends \Magento\Framework\App\Helper\AbstractHelper
{
    const NAMES_PREFIX = 'smlpgn_';
    const DEFAULT_STORE_ID = 0;
    const DEFAULT_CATEGORY_ID = 2;
    const DEFAULT_PRODUCT_PRICE = '100';
    const DEFAULT_PRODUCT_WEIGHT = '2';
    const DEFAULT_PRODUCT_QTY = '50';
    const CONFIGURABLE_PRODUCTS_PERCENT = 0.3;
    const CONFIGURABLE_CHILD_LIMIT = 2;
    const CONFIGURABLE_ATTRIBUTE = 'color';
    const ATTRIBUTE_SET = 11;
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


    protected $processedProducts = 0;

    protected $availableCategories;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\ConfigurableProduct\Api\Data\OptionInterface;
     */
    protected $configurableOption;

    /**
     * @var \Magento\Catalog\Api\Data\ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Api\Data\OptionValueInterface
     */
    protected $optionValue;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;


    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        OptionInterface $configurableOption,
        ProductExtensionFactory $productExtensionFactory,
        OptionValueInterface $optionValue,
        ProductRepositoryInterface $productRepository,
        $parameters
    )
    {
        $this->parameters = $parameters;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->attributeRepository = $attributeRepository;
        $this->configurableOption = $configurableOption;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->optionValue = $optionValue;
        $this->productRepository = $productRepository;
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

    /**
     * Inits product generation process
     */
    public function createProducts()
    {
        if ($this->getCount() > 1) {

            // Create configurable products at first
            $configurablesCount = round($this->getCount() * self::CONFIGURABLE_PRODUCTS_PERCENT);
            for ($createdConfigurables = 0; $createdConfigurables < $configurablesCount; $createdConfigurables++) {
                $this->createConfigurableProduct();
            }

            // Then create separate simple products
            while ($this->processedProducts < $this->getCount()) {
                $this->createSimpleProduct();
            }

        } else {
            $this->createSimpleProduct();
        }

    }

    /**
     * Creates a simple product
     *
     * @param bool|false $forceDefaultCategory
     * @param bool|false $doNotSave
     * @return \Magento\Catalog\Model\Product
     * @throws \Exception
     */
    public function createSimpleProduct($forceDefaultCategory = false, $doNotSave = false)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');

        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setStoreId(self::DEFAULT_STORE_ID)
            ->setAttributeSetId(self::ATTRIBUTE_SET)
            ->setName(self::NAMES_PREFIX . $this->titlesGenerator->generateProductTitle())
            ->setPrice(self::DEFAULT_PRODUCT_PRICE)
            ->setWeight(self::DEFAULT_PRODUCT_WEIGHT)
            ->setSku(uniqid());

        $productCategories = [];
        if ($forceDefaultCategory) {
            $productCategories = [self::DEFAULT_CATEGORY_ID];
        } else {
            /** @var \Magento\Catalog\Model\Product $categoryProduct */
            $categoryProduct = $this->getProductCategory();

            /* Get random category for product */
            if ($categoryProduct->getId() != self::DEFAULT_CATEGORY_ID) {
                $productCategories[] = $categoryProduct->getId();
            }

            /* Also assign current product to the default category */
            $productCategories[] = self::DEFAULT_CATEGORY_ID;
        }

        $product->setCategoryIds($productCategories);
        if (false == $doNotSave) {
            $this->productRepository->save($product);
        }

        $this->processedProducts++;

        return $product;
    }

    /**
     *
     * Creates a configurable product with child simple products
     *
     * @throws \Exception
     */
    public function createConfigurableProduct()
    {
        if ($this->processedProducts >= $this->getCount()) {
            return;
        }

        $configurableAttribute = $this->attributeRepository->get(self::CONFIGURABLE_ATTRIBUTE);

        if (null == $configurableAttribute) {
            throw new \Exception("Selected configurable attribute {self::CONFIGURABLE_ATTRIBUTE} is not available");
        }

        $childProductsData = $this->createConfigurableChildren($configurableAttribute);

        /** @var \Magento\Catalog\Model\Product $configurableProduct */
        $configurableProduct = $this->objectManager->create('Magento\Catalog\Model\Product');
        $configurableProduct
            ->setStoreId(self::DEFAULT_STORE_ID)
            ->setTypeId('configurable')
            ->setAttributeSetId(self::ATTRIBUTE_SET)
            ->setName(self::NAMES_PREFIX . $this->titlesGenerator->generateProductTitle() . ' configurable')
            ->setPrice(self::DEFAULT_PRODUCT_PRICE)
            ->setWeight(self::DEFAULT_PRODUCT_WEIGHT)
            ->setSku(uniqid())
            ->setCategoriesIds([$this->getProductCategory()]);

        $configurableOption = $this->configurableOption;
        $configurableOption->setAttributeId($configurableAttribute->getAttributeId())
            ->setLabel('Color')
            ->setValues($childProductsData['configurable_options_values']);

        $extensionAttributes = $configurableProduct->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->productExtensionFactory->create();
        }

        $extensionAttributes->setConfigurableProductLinks($childProductsData['child_products_ids']);
        $extensionAttributes->setConfigurableProductOptions([$configurableOption]);
        $configurableProduct->setExtensionAttributes($extensionAttributes);

        $this->productRepository->save($configurableProduct);

        $this->processedProducts++;
    }

    /**
     * Returns a random product category
     *
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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

    /**
     * Generates child products for configurable product
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $configurableAttribute
     * @return array
     * @throws \Exception
     */
    protected function createConfigurableChildren($configurableAttribute)
    {
        $availableOptions = $configurableAttribute->getOptions();

        /* Not sure why the zero index has no value for all attributes. Maybe will be fixed
           in next Magento versions */
        unset($availableOptions[0]);

        if (!count($availableOptions) > 0) {
            throw new \Exception('The selected configurable attribute has no values');
        }
        // Create child simple products
        $availableProductsCount = $this->getCount() - $this->processedProducts - 1;

        if ($availableProductsCount >= self::CONFIGURABLE_CHILD_LIMIT) {
            $childrenLimit = self::CONFIGURABLE_CHILD_LIMIT;
        } else {
            $childrenLimit = $availableProductsCount;
        }

        if ($childrenLimit > count($availableOptions)) {
            $childrenLimit = count($availableOptions);
        }

        $childrenCount = rand(1, $childrenLimit);
        $childProductsIds = $configurableOptionsValues = [];

        for($optCount = 0; $optCount < $childrenCount; $optCount++ ) {
            $product = $this->createSimpleProduct(true, true);
            $currentOptionId = array_rand($availableOptions);
            $optValueId = $availableOptions[$currentOptionId]->getValue();
            unset($availableOptions[$currentOptionId]);
            echo "$currentOptionId Value id is $optValueId \n";
            //var_dump(array_keys($availableOptions));

            $product->setCustomAttribute($configurableAttribute->getAttributeCode(), $optValueId);
            $optionValue = $this->optionValue;
            $optionValue->setValueIndex($optValueId);
            $configurableOptionsValues[] = $optionValue;
            $product = $this->productRepository->save($product);
            $childProductsIds[] = $product->getId();
        }

        return [
            'child_products_ids'            => $childProductsIds,
            'configurable_options_values'   => $configurableOptionsValues
        ];
    }

    /**
     * Returns products number to generate
     *
     * @return mixed
     */
    protected function getCount()
    {
        return $this->parameters['count'];
    }

    /**
     * Removes all generated products
     *
     * @return bool
     */
    protected function removeGeneratedItems()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product'); // FIXME change to get

        $productsCollection = $product->getCollection()
            ->addAttributeToFilter('name', ['like' => self::NAMES_PREFIX . '%']);

        foreach ($productsCollection as $product) {
            $this->productRepository->delete($product);
       }

        return true;
    }
}