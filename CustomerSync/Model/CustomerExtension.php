<?php
declare(strict_types=1);

namespace Pixafy\CustomerSync\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Pixafy\CustomerSync\Api\Data\CustomerExtensionInterface;

class CustomerExtension extends AbstractSimpleObject implements CustomerExtensionInterface
{
    /**
     * @return bool|null
     */
    public function getEligibleForSync(): ?bool
    {
        return $this->_get('eligible_for_sync');
    }

    /**
     * @param bool $eligibleForSync
     * @return void
     */
    public function setEligibleForSync($eligibleForSync): void
    {
        $this->setData('eligible_for_sync', $eligibleForSync);
    }
}
