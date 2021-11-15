<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

class Delete extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $rule = $this->getRule();

            try {
                if ($rule->getId()) {
                    $rule->delete();
                    $this->messageManager->addSuccessMessage(
                        __('Rule has been successfully deleted')
                    );
                } else {
                    $this->messageManager->addErrorMessage(__('Unable to find the rule'));
                }

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(self::DEFAULT_ERROR_MESSAGE));
            }
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
