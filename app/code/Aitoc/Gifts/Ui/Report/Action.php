<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Ui\Report;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $this->getUrl('aitoc_gifts/rule/edit', ['rule_id' => $row->getRuleId()]), $row->getName()
        );
    }
}
