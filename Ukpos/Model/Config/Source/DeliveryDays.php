<?php

namespace Ahmed\Ukpos\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DeliveryDays implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'wd', 'label' => __('Weekdays')],
            ['value' => 'sa', 'label' => __('Saturday')],
            ['value' => 'su', 'label' => __('Sunday')],
        ];
    }
}
