<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml\Manage;

/**
 * Class NewAction
 * @package Wyomind\PointOfSale\Controller\Adminhtml\Manage
 */
class NewAction extends \Wyomind\PointOfSale\Controller\Adminhtml\PointOfSale
{

    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->_resultForwardFactory->create()->forward("edit");
    }
}
