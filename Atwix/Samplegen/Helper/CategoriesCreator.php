<?php

namespace Atwix\Samplegen\Helper;

use Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Registry;


class CategoriesCreator extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CAT_NAME_PREFIX = 'smlpgn_';
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

    protected $storeId;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        $parameters
    )
    {
        $this->parameters = $parameters;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->titlesGenerator = $objectManager->create('Atwix\Samplegen\Helper\TitlesGenerator');
        parent::__construct($context);
    }

    public function launch()
    {
        $this->storeId = 0; // TODO: get default store id somehow
        $this->registry->register('isSecureArea', true);
        $rootCategoryId = $this->objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore(
            $this->storeId
        )->getRootCategoryId();

        if (false == $this->parameters['removeall']) {
            //for ($catsCount = $this->parameters['count']; $catsCount >= 0; $catsCount--) {
           return $this->createCategory($rootCategoryId);
            // }
        } else {
            return $this->removeGeneratedCategories();
        }
    }

    protected function createCategory($parentId)
    {
        //for ($depth = $this->parameters['depth']; $depth >= 0; $depth--) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->objectManager->create('Magento\Catalog\Model\Category');
            $category->setStoreId($this->storeId);
            $category->setParentId($parentId);
            $category->setName(self::CAT_NAME_PREFIX . $this->titlesGenerator->generateCategoryTitle());
            $category->save();
            //$this->createCategory($category->getId());
            return $category->getId();
        //}
    }

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
}