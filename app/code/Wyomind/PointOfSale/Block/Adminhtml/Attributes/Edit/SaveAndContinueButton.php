<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Block\Adminhtml\Attributes\Edit;

/**
 * Class SaveAndContinueButton
 * @package Wyomind\PointOfSale\Block\Adminhtml\Attributes\Edit
 */
class SaveAndContinueButton extends GenericButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('save_and_continue_edit')) {
            $data =  [
                'label' => __('Save and Continue Edit'),
                'on_click' => '',
                'class' => 'save',
                'sort_order' => 40
            ];
        }
        
        return $data;
    }
}