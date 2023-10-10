<?php
declare(strict_types=1);

namespace Pixafy\CustomerSync\Api\Data;

interface CustomerExtensionInterface
{
    /**
     * @return bool|null
     */
    public function getEligibleForSync(): ?bool;

    /**
     * @param bool $eligibleForSync
     * @return void
     */
    public function setEligibleForSync($eligibleForSync): void;
}
