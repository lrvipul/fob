<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer;

/**
 * Class Action
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        $this->getColumn()->setActions(
            [
                [
                    'url' => $this->getUrl('*/manage/edit', ['id' => $row->getPlace_id()]),
                    'caption' => __('Edit'),
                ],
                [
                    'url' => $this->getUrl('*/manage/delete', ['id' => $row->getPlace_id()]),
                    'confirm' => __('Are you sure you want to delete this pos / warehouse ?'),
                    'caption' => __('Delete'),
                ],
            ]
        );
        return parent::render($row);
    }
}
