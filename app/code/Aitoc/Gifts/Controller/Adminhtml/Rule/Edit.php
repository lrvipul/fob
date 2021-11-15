<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

class Edit extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $rule = $this->getRule();
        try {
            $resultPage = $this->initPage();
            $pageTitle = $rule->getId() ? __('Edit Rule')  :__('New Rule');
            $resultPage->getConfig()->getTitle()->prepend($pageTitle);
            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectIndex();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__(self::DEFAULT_ERROR_MESSAGE));
            return $this->redirectIndex();
        }
    }
}
