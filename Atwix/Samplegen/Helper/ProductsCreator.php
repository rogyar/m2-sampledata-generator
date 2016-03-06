<?php

namespace Atwix\Samplegen\Helper;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\CatalogWidget\Model\Rule\Condition\ProductFactory; // fixme: that's not exactly the needed factory
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;
use Atwix\Samplegen\Model\EntityGeneratorContext as Context;
use Magento\Catalog\Model\Product\Type as Type;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;

// TODO add a separate atribute instead a name prefix for all items

class ProductsCreator extends \Atwix\Samplegen\Helper\EntitiesCreatorAbstract
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var array
     */
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

    /**
     * @var array
     */
    protected $processedProducts = 0;


    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        OptionInterface $configurableOption,
        ProductExtensionFactory $productExtensionFactory,
        OptionValueInterface $optionValue,
        ProductRepositoryInterface $productRepository,
        Registry $registry
    )
    {
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->attributeRepository = $attributeRepository;
        $this->configurableOption = $configurableOption;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->optionValue = $optionValue;
        $this->productRepository = $productRepository;

        parent::__construct($context);

        $this->registry = $registry;
    }


    /**
     * Inits product generation process
     */
    public function createEntities()
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
     * Removes all generated products
     *
     * @return bool
     */
    public function removeEntities()
    {
        $product = $this->objectManager->create('Magento\Catalog\Model\Product'); // FIXME change to get

        $productsCollection = $product->getCollection()
            ->addAttributeToFilter('name', ['like' => self::NAMES_PREFIX . '%']);

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($productsCollection as $product) {
            $this->productRepository->delete($product);
       }

        return true;
    }
}