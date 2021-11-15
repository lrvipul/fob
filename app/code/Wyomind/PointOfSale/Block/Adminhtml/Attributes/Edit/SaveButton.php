<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Block\Adminhtml\Attributes\Edit;

/**
 * Class SaveButton
 * @package Wyomind\PointOfSale\Block\Adminhtml\Attributes\Edit
 */
class SaveButton extends GenericButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('save')) {
            $data =  [
                'label' => __('Save'),
                'on_click' => '',
                'class' => 'save primary'
            ];
        }
        
        return $data;
    }
}