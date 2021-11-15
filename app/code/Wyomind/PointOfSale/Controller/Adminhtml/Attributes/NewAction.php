<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml\Attributes;

/**
 * Class NewAction
 * @package Wyomind\PointOfSale\Controller\Adminhtml\Attributes
 */
class NewAction extends \Wyomind\PointOfSale\Controller\Adminhtml\Attributes
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}