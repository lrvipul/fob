<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml\Attributes;

/**
 * Class Index
 * @package Wyomind\PointOfSale\Controller\Adminhtml\Attributes
 */
class Index extends \Wyomind\PointOfSale\Controller\Adminhtml\Attributes
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_initAction(__('Attributes'));
        $this->_view->renderLayout();
    }
}