<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

class Activate extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $rule = $this->getRule();
            $action = $this->getRequest()->getParam('action');

            try {
                if ($rule->getId()) {
                    switch ($action) {
                        case 'activate':
                            $rule->setIsActive(1);
                            break;
                        case 'deactivate':
                            $rule->setIsActive(0);
                            break;
                    }

                    $rule->save();
                    $this->messageManager->addSuccessMessage(
                        __('Rule has been successfully %1', ($action == 'activate' ? 'activated' : 'deactivated'))
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
