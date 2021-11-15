<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

class AvailableProductOptions implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            [
                'value' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'label' => __('Simple')
            ],
            [
                'value' => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                'label' => __('Virtual')
            ],
            [
                'value' => 'downloadable',
                'label' => __('Downloadable')
            ]
        );
    }
}
