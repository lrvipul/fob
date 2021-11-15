<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer;

class Activity extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $htmlData = [
            'url' => '',
            'label' => '',
        ];

        switch ($row->getIsActive()) {
            case 1:
                $htmlData = [
                    'url' => $this->getUrl(
                        'aitoc_gifts/rule/activate',
                        ['rule_id' => $row->getRuleId(), 'action' => 'deactivate']),
                    'label' => __('Deactivate'),
                ];
                break;
            case 0:
                $htmlData = [
                    'url' => $this->getUrl(
                        'aitoc_gifts/rule/activate',
                        ['rule_id' => $row->getRuleId(), 'action' => 'activate']),
                    'label' => __('Activate'),
                ];
                break;
        }

        return <<<HTML
<a href="{$htmlData['url']}">{$htmlData['label']}</a>
HTML;
    }
}
