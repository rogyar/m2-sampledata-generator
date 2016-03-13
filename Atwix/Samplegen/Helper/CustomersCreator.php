<?php

namespace Atwix\Samplegen\Helper;

use Atwix\Samplegen\Model\EntityGeneratorContext as Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;


class CustomersCreator extends \Atwix\Samplegen\Helper\EntitiesCreatorAbstract
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /** @var  array */
    protected $websiteIds;

    public function __construct(
        Context $context,
        Registry $registry,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
        $this->registry = $registry;
    }


    public function createEntities()
    {
        for ($cnt = 0; $cnt < $this->getCount(); $cnt++) {
            $this->createCustomer();
        }
    }

    public function createCustomer()
    {
        /** @var \Magento\Customer\Model\Customer  $customer */
        $customer = $this->customerFactory->create();
        $customerName = explode(' ', $this->titlesGenerator->generateCustomerName());

        $customer->setFirstname($customerName[0]);
        $customer->setLastname($customerName[1]);
        $customer->setEmail(self::NAMES_PREFIX . uniqid() . '@' . self::DEFAULT_EMAIL_DOMAIN);
        $customer->setWebsiteId($this->storeManager->getWebsite()->getWebsiteId());

        $customer->save($customer);

        return $customer;
    }

    /**
     * Removes all previously generated customers by this tool
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeEntities()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer'); // FIXME change to get

        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customersCollection */
        $customersCollection = $customer->getCollection();
        $generatedCustomers = $customersCollection->addAttributeToFilter('email',
            ['like' => self::NAMES_PREFIX . '%']);

        /** @var \Magento\Customer\Model\Customer $generatedCustomer */
        $generatedCustomers = $generatedCustomers->getItems();
        foreach ($generatedCustomers as $generatedCustomer) {
            // TODO: use repository instead
            $generatedCustomer->delete();
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