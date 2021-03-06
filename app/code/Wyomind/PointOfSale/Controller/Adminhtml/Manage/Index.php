<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml\Manage;

/**
 * Class Index
 * @package Wyomind\PointOfSale\Controller\Adminhtml\Manage
 */
class Index extends \Wyomind\PointOfSale\Controller\Adminhtml\PointOfSale
{

    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Sales::sales");
        $resultPage->getConfig()->getTitle()->prepend(__('Points Of Sale / Warehouses'));
        return $resultPage;
    }
}
