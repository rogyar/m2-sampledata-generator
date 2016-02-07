<?php

namespace Atwix\Samplegen\Helper;

use Magento\Framework\App\Helper\Context;


class CategoriesCreator extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var $parameters array
     */
    protected $parameters;

    public function __construct(Context $context, $parameters)
    {
        $this->parameters = $parameters;
        parent::__construct($context);
    }

    public function launch()
    {
        return;
    }
}