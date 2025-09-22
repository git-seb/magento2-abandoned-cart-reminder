<?php

namespace Adeelq\AbandonedCartReminder\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DiscountSlider extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId = $element->getHtmlId();
        $value = $element->getValue() ?: 10;
        
        $html = '<div class="admin__field-control">';
        $html .= '<input type="range" 
                    id="' . $elementId . '_slider" 
                    min="0" 
                    max="100" 
                    step="1" 
                    value="' . $value . '" 
                    class="admin__control-slider"
                    style="width: 200px; margin-right: 10px;"
                    onchange="document.getElementById(\'' . $elementId . '\').value = this.value; document.getElementById(\'' . $elementId . '_display\').innerHTML = this.value + \'%\';">';
        $html .= '<input type="hidden" 
                    id="' . $elementId . '" 
                    name="' . $element->getName() . '" 
                    value="' . $value . '" 
                    class="' . $element->getClass() . '">';
        $html .= '<span id="' . $elementId . '_display" style="font-weight: bold; color: #007cba;">' . $value . '%</span>';
        $html .= '</div>';
        
        return $html;
    }
}