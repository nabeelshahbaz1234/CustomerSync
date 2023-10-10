<?php
declare(strict_types=1);

namespace Pixafy\CustomerSync\Observer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Executes on order status change
 *
 * Class AfterPlaceOrder
 */
class UpdateEligibleForSyncObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $_orderCollectionFactory;

    /**
     * @var CustomerRepository
     */
    private CustomerRepositoryInterface $customerRepository;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $orderCollectionFactory;

    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CollectionFactory           $orderCollectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws Exception
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $customerId = $order->getCustomerId();
        $this->updateLoyalAttribute($customerId);
    }

    /**
     * Update the Attribute thorugh customer Id
     *
     * @param $customerId
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputMismatchException
     */
    private function updateLoyalAttribute($customerId): void
    {
        $collection = $this->countOrders($customerId);
        $customer = $this->customerRepository->getById($customerId);
        if (count($collection) > 0) {
            $customer->setCustomAttribute('eligible_for_sync', 1);
        } else {
            $customer->setCustomAttribute('eligible_for_sync', 0);
        }
        $this->customerRepository->save($customer);
    }

    /**
     * Count the order
     *
     * @param $customerId
     * @return Collection
     */
    private function countOrders($customerId): Collection
    {
        return $this->orderCollectionFactory->create($customerId);
    }
}
