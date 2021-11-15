<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Aitoc\Gifts\Model\Rule;

class RuleActiveOptions implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Rule::RULE_INACTIVE,
                'label' => __('Inactive')
            ),
            array(
                'value' => Rule::RULE_ACTIVE,
                'label' => __('Active')
            )
        );
    }
}
