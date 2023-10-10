<?php
declare(strict_types=1);

namespace Pixafy\CustomerSync\Ui\Component\Listing\Column;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AttributeFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * @class SyncAttribute
 */
class SyncAttribute extends Column
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var AttributeFactory
     */
    private AttributeFactory $attributeFactory;
    /**
     * @var array
     */
    private array $data;
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerFactory $customerFactory
     * @param AttributeFactory $attributeFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface            $context,
        UiComponentFactory          $uiComponentFactory,
        CustomerFactory             $customerFactory,
        AttributeFactory            $attributeFactory,
        CustomerRepositoryInterface $customerRepository,
        array                       $components = [],
        array                       $data = []
    ) {
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->context = $context;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->attributeFactory = $attributeFactory;
        $this->components = $components;
        $this->data = $data;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $customerId = $item['entity_id'];
                $customer = $this->customerRepository->getById($customerId);
                $eligibleForSync = $customer->getCustomAttribute('eligible_for_sync');
                if ($eligibleForSync !== null && $eligibleForSync->getValue() !== null) {
                    $item[$this->getData('name')] = 'Yes';
                } else {
                    $item[$this->getData('name')] = 'No';
                }
            }
        }
        return $dataSource;
    }
}
