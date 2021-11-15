<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Promotions;

class Total extends \Magento\Backend\App\Action
{
    /**
     * General report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Aitoc_Gifts::analytics'
        )->_addBreadcrumb(
            __('Statistic'),
            __('Statistic')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Statistic'));
        $this->_view->renderLayout();
    }

    /**
     * Acl admin user check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aitoc_Gifts::analytics');
    }
}
