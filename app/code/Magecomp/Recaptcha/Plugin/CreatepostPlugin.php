<?php

namespace Magecomp\Recaptcha\Plugin;
use Magento\Framework\Controller\ResultFactory;
use Magecomp\Recaptcha\Helper\Data as RecaptchaHelper;

class CreatepostPlugin
{
    protected $request;
    protected $redirect;
    protected $resultFactory;
    protected $messageManager;
    protected $recaptchaHelper;
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RecaptchaHelper $recaptchaHelper

    ) {
        $this->request = $request;
        $this->redirect = $redirect;
        $this->resultFactory=$resultFactory;
        $this->messageManager = $messageManager;
        $this->recaptchaHelper = $recaptchaHelper;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\CreatePost $subject, \Closure $proceed)
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->redirect->getRefererUrl());

        try {
            if ($this->recaptchaHelper->IS_CUSTREG()) {
                $data = $this->request->getParams();
                if ($this->recaptchaHelper->getCaptchaverify($data) === true || $this->recaptchaHelper->getCaptchaverify($data) == '1') {
                    return $proceed();
                } else {
                    $this->messageManager->addErrorMessage('Invalid Recaptcha');
                    return $resultRedirect;
                }
            }
            else
            {
                return $proceed();
            }
        }
        catch (\Exception $e)
        {
            $this->messageManager->addErrorMessage('Invalid Recaptcha');
            return $resultRedirect;
        }
    }
}