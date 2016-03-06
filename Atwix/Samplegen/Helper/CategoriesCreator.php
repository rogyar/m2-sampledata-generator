<?php

namespace Atwix\Samplegen\Helper;

use Atwix\Samplegen\Model\EntityGeneratorContext as Context;
use Magento\Framework\Registry;


class CategoriesCreator extends \Atwix\Samplegen\Helper\EntitiesCreatorAbstract
{
    /**
     * @var $parameters array
     */
    protected $parameters; //todo: remove

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager; //todo: remove

    /**
     * @var \Atwix\Samplegen\Helper\TitlesGenerator
     */
    protected $titlesGenerator; //todo: remove

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $processedCategoriesCount = 0;

    public function __construct(
        Context $context,
        Registry $registry
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
    }


    public function createEntities()
    {
        $this->normalizeDepth();
        $defaultCategory = $this->objectManager->create('Magento\Catalog\Model\Category')
            ->load(self::DEFAULT_CATEGORY_ID);
        while ($this->parameters['count'] >= $this->processedCategoriesCount) {
            $currentCategory = $defaultCategory;
            for ($depth = 0; $depth < $this->parameters['depth']; $depth++) {
                $currentCategory = $this->createCategory($currentCategory);
                $this->processedCategoriesCount++;
            }
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
            ->setName(self::NAMES_PREFIX . $this->titlesGenerator->generateCategoryTitle())
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
    public function removeEntities()
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->objectManager->create('Magento\Catalog\Model\Category'); // FIXME change to get

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoriesCollection */
        $categoriesCollection = $category->getCollection();
        $generatedCategories = $categoriesCollection->addAttributeToFilter('name',
            ['like' => self::NAMES_PREFIX . '%']);

        /** @var \Magento\Catalog\Model\Category $generatedCategory */
        $generatedCategories = $generatedCategories->getItems();
        foreach ($generatedCategories as $generatedCategory) {
            // TODO: use repository instead
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