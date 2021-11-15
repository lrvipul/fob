<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer;

class CouponType extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Aitoc\Gifts\Model\Rule\CouponOptions
     */
    private $couponOptions;

    /**
     * CouponType constructor.
     *
     * @param \Magento\Backend\Block\Context         $context
     * @param \Aitoc\Gifts\Model\Rule\CouponOptions $couponOptions
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Aitoc\Gifts\Model\Rule\CouponOptions $couponOptions,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->couponOptions = $couponOptions;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $label = '';

        if ($row->getCouponType()) {
            foreach ($this->couponOptions->toOptionArray() as $option) {
                if ($option['value'] == $row->getCouponType()) {
                    $label = $option['label'];
                }
            }
        }

        return $label;
    }
}
