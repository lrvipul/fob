<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Controller\Adminhtml\Stocks;

/**
 * Index action
 */
class Index extends \Wyomind\AdvancedInventory\Controller\Adminhtml\Stocks
{

    /**
     * Execute action
     */
    public function execute()
    {

        if (!$this->_permissionsHelper->hasAllPermissions()) {
            $this->messageManager->addError(__("You are not allowed to view any POS/WH<br/>Please check the user permissions: ")."<a href='".$this->_url->getUrl("advancedinventory/permissions/index")."'>".__("Permissions")."</a>");
        }


        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Wyomind_AdvancedInventory::stocks");
        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Inventory > Manage stocks'));
        $resultPage->addBreadcrumb(__('Advanced Inventory'), __('Advanced Inventory'));
        $resultPage->addBreadcrumb(__('Manage stocks'), __('Manage Stocks'));

        return $resultPage;
    }
}
