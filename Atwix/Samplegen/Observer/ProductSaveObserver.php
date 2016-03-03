<?php

namespace Atwix\Samplegen\Observer;

use Magento\Framework\Event\ObserverInterface;
use Atwix\Samplegen\Helper\ProductsCreator;

class ProductSaveObserver implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $websiteIds;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Assigns a sample data product to all websites
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product  $product */
        $product = $observer->getEvent()->getProduct();
        if (strpos($product->getName(), ProductsCreator::NAMES_PREFIX) !== false) {
            $product->setWebsiteIds($this->getWebsiteIds());
            $product->setStockData([
                'is_in_stock' => 1,
                'use_config_manage_stock' => 0, //'Use config settings' checkbox
                'manage_stock' => 1, //manage stock
                'qty' => 100
            ]);
        }
    }

    protected function getWebsiteIds()
    {
        if (null == $this->websiteIds) {
            $websitesList = $this->storeManager->getWebsites(true);
            $this->websiteIds = array_keys($websitesList);
        }

        return $this->websiteIds;
    }
}
