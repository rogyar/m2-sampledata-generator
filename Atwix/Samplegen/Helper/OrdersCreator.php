<?php

namespace Atwix\Samplegen\Helper;

use Atwix\Samplegen\Model\EntityGeneratorContext as Context;
use Magento\Framework\Registry;
use \Magento\Quote\Model\QuoteFactory;
use \Magento\Customer\Model\CustomerFactory;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Quote\Model\QuoteManagement;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Model\Service\OrderService;
use Atwix\Samplegen\Helper\ProductsCreator;

// TODO add a separate atribute instead a name prefix for all items

class OrdersCreator extends \Atwix\Samplegen\Helper\EntitiesCreatorAbstract
{

    const ORDER_SHIPPING_METHOD_CODE = 'freeshipping_freeshipping';
    const ORDER_PAYMENT_METHOD_CODE = 'checkmo';

    protected $orderSampleData = [
        'address' => [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'street' => 'somestreet',
            'city' => 'somecity',
            'country_id' => 'US',
            'region' => 'someregion',
            'postcode' => '91910',
            'telephone' => '111222333',
            'fax' => '111222333',
            'save_in_address_book' => 1
        ]
    ];

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Atwix\Samplegen\Helper\CustomersCreator
     */
    protected $customersCreator;

    /**
     * @var \Atwix\Samplegen\Helper\ProductsCreator
     */
    protected $productsCreator;

    public function __construct(
        Context $context,
        Registry $registry,
        QuoteFactory $quoteFactory,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        QuoteManagement $quoteManagement,
        ProductFactory $productFactory,
        FormKey $formKey,
        OrderService $orderService,
        CustomersCreator $customersCreator,
        ProductsCreator $productsCreator
    )
    {
        $this->quoteFactory = $quoteFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->quoteManagement = $quoteManagement;
        $this->productFactory = $productFactory;
        $this->formKey = $formKey;
        $this->orderService = $orderService;
        $this->customersCreator = $customersCreator;
        $this->productsCreator = $productsCreator;

        parent::__construct($context);
        $this->registry = $registry;
    }


    /**
     * Inits product generation process
     */
    public function createEntities()
    {
        for ($cnt = 0; $cnt < $this->getCount(); $cnt++) {
            $this->createOrder();
        }
    }

   public function createOrder()
   {
       $store = $this->storeManager->getStore();
       $websiteId = $this->storeManager->getStore()->getWebsiteId();
       $customer = $this->getCustomer();
       $quote = $this->quoteFactory->create();
       $quote->setStore($store);
       $quote->assignCustomer($customer);
       //$quote->setCurrency();
       $this->addItemsToQuote($quote);

       $quote->getBillingAddress()->addData($this->orderSampleData['address']);
       $quote->getShippingAddress()->addData($this->orderSampleData['address']);

       $shippingAddress = $quote->getShippingAddress();
       $shippingAddress->setCollectShippingRates(true)
           ->collectShippingRates()
           ->setShippingMethod(self::ORDER_SHIPPING_METHOD_CODE);
       $quote->setPaymentMethod(self::ORDER_PAYMENT_METHOD_CODE);
       $quote->setInventoryProcessed(false);
       $quote->save();

       $quote->getPayment()->importData(['method' => self::ORDER_PAYMENT_METHOD_CODE]);
       $quote->collectTotals()->save();

       $order = $this->quoteManagement->submit($quote);
       $order->setEmailSent(0);
   }

    protected function getCustomer()
    {
        $customer = $this->customerFactory->create();

        /* Check if there are some generated sample customers */
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customersCollection */
        $customersCollection = $customer->getCollection()->addAttributeToFilter('email',
            ['like' => self::NAMES_PREFIX . '%']);
        if ($customersCollection->getSize() > 0) {
            return $customersCollection->getFirstItem();
        }

        /* If there are no generated customers - try to create one */
        return $this->customersCreator->createCustomer();
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function addItemsToQuote($quote)
    {
        /* Check if there are some generated sample products */
        $product = $this->productFactory->create();
        $productsCollection = $product->getCollection()->addAttributeToFilter(
            'name', ['like' => self::NAMES_PREFIX . '%']
        );

        if ($productsCollection->getSize() > 0) {
            $product = $productsCollection->getFirstItem();
        } else {
            /* If there are no generated products - try to create one */
            $product = $this->productsCreator->createSimpleProduct();
        }

        $quote->addProduct($product);
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