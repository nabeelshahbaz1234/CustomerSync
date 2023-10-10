<?php
declare(strict_types=1);

namespace Pixafy\CustomerSync\Plugin\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;

/**
 * @class AddEligibleForSyncAttributePlugin
 */
class AddEligibleForSyncAttributePlugin
{
    /**
     * @var CustomerExtensionFactory
     */
    private CustomerExtensionFactory $customerExtensionFactory;

    /**
     * @param CustomerExtensionFactory $customerExtensionFactory
     */
    public function __construct(
        CustomerExtensionFactory $customerExtensionFactory
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterGet(
        CustomerRepositoryInterface $subject,
        CustomerInterface           $customer
    ) {
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->customerExtensionFactory->create();
        }
        $customer->setExtensionAttributes($extensionAttributes);
        return $customer;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerSearchResultsInterface $searchResults
     * @return CustomerSearchResultsInterface
     */
    public function afterGetList(
        CustomerRepositoryInterface    $subject,
        CustomerSearchResultsInterface $searchResults
    ) {
        $customers = $searchResults->getItems();
        foreach ($customers as &$customer) {
            $extensionAttributes = $customer->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->customerExtensionFactory->create();
            }
            $customer->setExtensionAttributes($extensionAttributes);
        }
        return $searchResults;
    }
}
