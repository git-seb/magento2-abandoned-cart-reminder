<?php

namespace Adeelq\AbandonedCartReminder\Model\Adminhtml\Config\Source\System;

use Magento\Framework\Data\OptionSourceInterface;

class EmailSender implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'general', 'label' => __('General Contact')],
            ['value' => 'sales', 'label' => __('Sales Representative')],
            ['value' => 'support', 'label' => __('Customer Support')],
            ['value' => 'custom1', 'label' => __('Custom Email 1')],
            ['value' => 'custom2', 'label' => __('Custom Email 2')],
        ];
    }
}