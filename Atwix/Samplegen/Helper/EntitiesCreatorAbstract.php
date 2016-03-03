<?php

namespace Atwix\Samplegen\Helper;

use \Atwix\Samplegen\Model\EntityGeneratorContext as Context;
use \Atwix\Samplegen\Console\Command\GenerateProductsCommand;
use Magento\Catalog\Model\Product\Type as Type;
use Magento\Catalog\Api\ProductRepositoryInterface;

class EntitiesCreatorAbstract
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



    public function __construct(Context $context)
    {
        $this->parameters = $context->getParameters();
        $this->objectManager = $context->getObjectManager();
        $this->registry = $context->getRegistry();
        $this->titlesGenerator = $context->getTitlesGenerator();
    }

    public function launch()
    {
        $this->registry->register('isSecureArea', true);

        if (false == $this->parameters[GenerateProductsCommand::INPUT_KEY_REMOVE]) {
            return $this->createEntities();
        } else {
            return $this->removeEntities();
        }
    }

    /**
     * Generates new entities
     *
     * @return bool
     */
    public function createEntities()
    {
        return true;
    }

    /**
     * Removes generated entities
     *
     * @return bool
     */
    public function removeEntities()
    {
        return true;
    }


    /**
     * Returns entities number to generate
     *
     * @return mixed
     */
    protected function getCount()
    {
        return $this->parameters['count'];
    }
}