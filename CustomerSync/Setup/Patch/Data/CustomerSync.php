<?php
declare(strict_types=1);

namespace Pixafy\CustomerSync\Setup\Patch\Data;

use Exception;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

/**
 * @class CustomerSync
 */
class CustomerSync implements DataPatchInterface
{
    /**
     * @var Config
     */
    private Config $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private AttributeSetFactory $attributeSetFactory;

    /**
     * AddressAttribute constructor.
     *
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config              $eavConfig,
        EavSetupFactory     $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     * @throws Exception
     */
    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create();

        $customerEntity = $this->eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $eavSetup->addAttribute('customer', 'eligible_for_sync', [
            'type'             => 'int',
            'input'            => 'boolean',
            'label'            => 'Eligible For Sync',
            'visible'          => false,
            'source'           => Boolean::class,
            'required'         => false,
            'user_defined'     => true,
            'system'           => false,
            'global'           => true,
            'default'          => null,
            'visible_on_front' => false,
            'position'         => 1030,
            'is_used_in_grid' => true,   //setting grid options
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true,
        ]);

        $customAttribute = $this->eavConfig->getAttribute('customer', 'eligible_for_sync');

        $customAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer', 'adminhtml_checkout', 'customer_account_create', 'customer_account_edit']
        ]);
        $customAttribute->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
