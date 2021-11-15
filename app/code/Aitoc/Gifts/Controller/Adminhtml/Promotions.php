<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Aitoc\Gifts\Controller\RegistryConstants;

abstract class Promotions extends Action
{
    const DEFAULT_ERROR_MESSAGE  = 'Something went wrong. See the error log.';

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /** @var \Magento\Framework\Registry */
    protected $registry;

    /** @var \Aitoc\FollowUp\Model\RuleFactory */
    protected $ruleFactory;

    /**
     * Rule constructor.
     *
     * @param Action\Context                             $context
     * @param \Aitoc\Gifts\Model\RuleFactory            $ruleFactory
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aitoc_Gifts::promotions');
        $resultPage->addBreadcrumb(__('Marketing'), __('Marketing'));
        return $resultPage;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function redirectIndex()
    {
        return $this->_redirect('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aitoc_Gifts::promotions');
    }
}
