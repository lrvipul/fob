<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer;

class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $url = $this->getUrl(
            'aitoc_gifts/rule/delete',
            ['rule_id' => $row->getRuleId()]);
        $label = __('Delete');

        return <<<HTML
<a href="{$url}">{$label}</a>
HTML;
    }
}
