<?php
declare(strict_types=1);

namespace Pixafy\CustomerSync\Console\Command;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @class CustomerListCommand
 */
class CustomerListCommand extends Command
{
    /**
     * @var CustomerCollectionFactory
     */
    private CustomerCollectionFactory $customerCollectionFactory;
    /**
     * @var OrderCollectionFactory
     */
    private OrderCollectionFactory $orderCollectionFactory;
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;
    /**
     * @var Config
     */
    private Config $eavConfig;
    /**
     * @var State
     */
    private State $state;

    /**
     * CustomerListCommand constructor.
     *
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $eavConfig
     * @param State $state
     */
    public function __construct(
        CustomerCollectionFactory   $customerCollectionFactory,
        OrderCollectionFactory      $orderCollectionFactory,
        CustomerFactory             $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        Config                      $eavConfig,
        State                       $state
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct();
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->eavConfig = $eavConfig;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('customer:list')
            ->setDescription('Get a list of customers within a given range of customer IDs.')
            ->addArgument(
                'start_customer_id',
                InputArgument::REQUIRED,
                'Start customer ID'
            )
            ->addArgument(
                'end_customer_id',
                InputArgument::REQUIRED,
                'End customer ID'
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startCustomerId = $input->getArgument('start_customer_id');
        $endCustomerId = $input->getArgument('end_customer_id');

        if (!is_numeric($startCustomerId) || !is_numeric($endCustomerId)) {
            $output->writeln('<error>Invalid start or end customer ID.</error>');
            return Cli::RETURN_FAILURE;
        }

        $startCustomerId = (int)$startCustomerId;
        $endCustomerId = (int)$endCustomerId;

        if ($startCustomerId > $endCustomerId) {
            $output->writeln('<error>Start customer ID cannot be greater than end customer ID.</error>');
            return Cli::RETURN_FAILURE;
        }

        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
            $customerCollection = $this->customerCollectionFactory->create();
            $customerCollection->addFieldToFilter('entity_id', ['gteq' => $startCustomerId]);
            $customerCollection->addFieldToFilter('entity_id', ['lteq' => $endCustomerId]);

            if ($customerCollection->getSize() > 0) {
                foreach ($customerCollection as $customer) {
                    $customerId = $customer->getId();

                    $orderCollection = $this->orderCollectionFactory->create();
                    $orderCollection->addFieldToFilter('customer_id', $customerId);
                    if ($orderCollection->getSize() > 0) {
                        // Customer has at least one order, check the attribute value
                        try {
                            $customer = $this->customerRepository->getById($customerId);
                            $eligibleForSync = $customer->getCustomAttribute('eligible_for_sync');
                            if ($eligibleForSync !== null && $eligibleForSync->getValue() == 1) {
                                // Attribute value is already 1, show error message
                                $output->writeln('<error>Error: Customer ID ' . $customerId . ' is already eligible.</error>');
                            } else {
                                // Attribute value is not 1, update it
                                $customer->setCustomAttribute('eligible_for_sync', 1);
                                $this->customerRepository->save($customer);
                                $output->writeln('<info>Customer attribute updated successfully for ID ' . $customerId . '</info>');
                            }
                        } catch (Exception $e) {
                            $output->writeln('<error>Error for customer ID ' . $customerId . ': ' . $e->getMessage() . '</error>');
                        }
                    } else {
                        $output->writeln('<comment>Customer ID ' . $customerId . ' does not have any orders.</comment>');
                    }
                    if ($orderCollection->getSize() > 0) {
                        $output->writeln('Orders:');

                        foreach ($orderCollection as $order) {
                            $output->writeln('Order ID: ' . $order->getId());
                            $output->writeln('Order Status: ' . $order->getStatus());
                            // Add any additional order information you want to display
                        }
                    } else {
                        $output->writeln('No orders found for this customer.');
                    }
                }
            } else {
                $output->writeln('<info>No customers found within the given range.</info>');
            }
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
