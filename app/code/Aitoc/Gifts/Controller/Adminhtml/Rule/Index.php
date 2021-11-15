<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

class Index extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Rules'));

        return $resultPage;
    }
}
