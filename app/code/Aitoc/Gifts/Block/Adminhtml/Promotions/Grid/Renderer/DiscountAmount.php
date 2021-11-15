<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer;

class DiscountAmount extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $label = '';

        switch ($row->getDiscountType()) {
            case \Aitoc\Gifts\Model\Rule\Type\DiscountType::DISCOUNT_AMOUNT_TYPE:
                $label = $row->getDiscountAmount();
                break;
            case \Aitoc\Gifts\Model\Rule\Type\DiscountType::DISCOUNT_PERCENT_TYPE:
                $label = (float)$row->getDiscountPercent() . '%';
                break;
            default:
                $label = $row->getDiscountAmount();
                break;
        }

        return $label;
    }
}
