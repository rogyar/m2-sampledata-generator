<?php

namespace Atwix\Samplegen\Model;

use Atwix\Samplegen\Helper\TitlesGenerator;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use \Magento\Store\Model\StoreManagerInterface;


class EntityGeneratorContext implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var TitlesGenerator
     */
    protected $titlesGenerator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Atwix\Samplegen\Helper\TitlesGenerator $titlesGenerator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Manager $moduleManager,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        TitlesGenerator $titlesGenerator,
        StoreManagerInterface $storeManager
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_logger = $logger;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->titlesGenerator = $titlesGenerator;
        $this->storeManager = $storeManager;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return TitlesGenerator
     */
    public function getTitlesGenerator()
    {
        return $this->titlesGenerator;
    }

    /**
     * @return \Magento\Framework\Module\Manager
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }
}
