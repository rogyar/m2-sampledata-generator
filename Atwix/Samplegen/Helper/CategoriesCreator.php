<?php

namespace Atwix\Samplegen\Helper;

use Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;


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

    protected $storeId;

    public function __construct(Context $context, ObjectManagerInterface $objectManager, $parameters)
    {
        $this->parameters = $parameters;
        $this->objectManager = $objectManager;
        $this->titlesGenerator = $objectManager->create('Atwix\Samplegen\Helper\TitlesGenerator');
        parent::__construct($context);
    }

    public function launch()
    {
        $this->storeId = 0; // TODO: get default store id somehow
        $rootCategoryId = $this->objectManager->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore(
            $this->storeId
        )->getRootCategoryId();

        //for ($catsCount = $this->parameters['count']; $catsCount >= 0; $catsCount--) {
            $this->createCategory($rootCategoryId);
       // }
        return;
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
}