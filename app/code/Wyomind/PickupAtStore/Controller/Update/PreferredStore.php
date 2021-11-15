<?php

namespace Wyomind\PickupAtStore\Controller\Update;


use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class PreferredStore extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * PreferredStore constructor.
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Session $customerSession
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

        if ($this->customerSession->isLoggedIn()) {
            $store = $this->getRequest()->getParam('store');
            $storeId = $store['id'];
            $customer = $this->customerSession->getCustomer();
            $customer->setPreferredStore($storeId);
            $customer->save();
        }
    }
}