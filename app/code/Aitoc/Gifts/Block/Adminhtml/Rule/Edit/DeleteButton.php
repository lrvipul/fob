<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Block\Adminhtml\Rule\Edit;

use Aitoc\Gifts\Controller\RegistryConstants;

class DeleteButton extends GenericButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $ruleId = $this->getRuleId();
        if ($ruleId) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete action-secondary',
                'on_click' => 'deleteConfirm("' . __('This rule will be deleted! Are you sure?') . '", "'
                    . $this->getDeleteUrl() . '")',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    private function getDeleteUrl()
    {
        return $this->urlBuilder
            ->getUrl('*/*/delete', [RegistryConstants::RULE_PARAM_URL_KEY => $this->getRuleId()]);
    }
}
