<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer;

class DiscountType extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Aitoc\Gifts\Model\Rule\Type\DiscountType
     */
    private $discountType;

    /**
     * DiscountType constructor.
     *
     * @param \Magento\Backend\Block\Context             $context
     * @param \Aitoc\Gifts\Model\Rule\Type\DiscountType $discountType
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Aitoc\Gifts\Model\Rule\Type\DiscountType $discountType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->discountType = $discountType;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $label = '';

        if ($row->getDiscountType()) {
            foreach ($this->discountType->toOptionArray() as $option) {
                if ($option['value'] == $row->getDiscountType()) {
                    $label = $option['label'];
                }
            }
        }

        return $label;
    }
}
