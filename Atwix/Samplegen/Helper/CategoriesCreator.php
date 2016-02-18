<?php

namespace Atwix\Samplegen\Helper;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Registry;


class CategoriesCreator extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CAT_NAME_PREFIX = 'smlpgn_';
    const DEFAULT_STORE_ID = 0;
    const DEFAULT_CATEGORY_ID = 2;
    /**
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

    //protected $eavSetup;

   // protected $defaultAttributeSetId; // FIXME: already present in Category model

    protected $processedCategoriesCount = 0;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        //EavSetup $eavSetup,
        $parameters
    )
    {
        $this->parameters = $parameters;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        //$this->eavSetup = $eavSetup;
        $this->titlesGenerator = $objectManager->create('Atwix\Samplegen\Helper\TitlesGenerator');
        parent::__construct($context);
    }

    public function launch()
    {
        $this->registry->register('isSecureArea', true);
        $rootCategoryId = $this->objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore(
            self::DEFAULT_STORE_ID
        )->getRootCategoryId();

        if (false == $this->parameters['removeall']) {
            $this->normalizeDepth();
            $defaultCategory = $this->objectManager->create('Magento\Catalog\Model\Category')
                ->load(self::DEFAULT_CATEGORY_ID);
//            $this->defaultAttributeSetId = $this->eavSetup->getDefaultAttributeSetId( // FIXME: already present in Category model
//                $this->eavSetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY)
//            );
            while ($this->parameters['count'] >= $this->processedCategoriesCount) {
                $currentCategory = $defaultCategory;
                for ($depth = 0; $depth < $this->parameters['depth']; $depth++) {
                    $currentCategory = $this->createCategory($currentCategory);
                    $this->processedCategoriesCount++;
                }
            }

            return true;

        } else {
            return $this->removeGeneratedCategories();
        }
    }

    /**
     * Creates a new child category for $parentCategory
     *
     * @param \Magento\Catalog\Model\Category $parentCategory
     * @return \Magento\Catalog\Model\Category
     */
    protected function createCategory($parentCategory)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->objectManager->create('Magento\Catalog\Model\Category');
        $category->setStoreId(self::DEFAULT_STORE_ID)
            ->setParentId($parentCategory->getId())
            ->setName(self::CAT_NAME_PREFIX . $this->titlesGenerator->generateCategoryTitle())
            ->setAttributeSetId($category->getDefaultAttributeSetId())
            ->setLevel($parentCategory->getLevel() + 1)
            ->setPath($parentCategory->getPath())
            ->setIsActive(1)
            ->save();

            return $category;
    }

    /**
     * Removes all previously generated categories by this tool
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function removeGeneratedCategories()
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->objectManager->create('Magento\Catalog\Model\Category'); // FIXME change to get

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

    /**
     * If the depth's value is not applicable - tweaks it
     */
    protected function normalizeDepth()
    {
        if (empty($this->parameters['depth'])) {
            $this->parameters['depth'] = 1;
        } elseif ($this->parameters['depth'] > $this->parameters['count']) {
            $this->parameters['depth'] = $this->parameters['count'];
        }
    }
}