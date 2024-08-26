<?php

namespace Ahmed\Ukpos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Get the configuration value for the specified path.
     *
     * @param string $field
     * @param null|int|string $storeId
     * @return mixed
     */
    public function getFlatRateConfig($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'carriers/flatrate/' . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFreeShippingConfig($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'carriers/freeshipping/' . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
