<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Store;

/**
 * Class Index
 * @package Wyomind\PointOfSale\Controller\Store
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Wyomind\PointOfSale\Model\PointOfsale
     */
    protected $_posModel;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Wyomind\PointOfSale\Model\PointOfsale $posModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Wyomind\PointOfSale\Model\PointOfsale $posModel
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_posModel = $posModel;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $pos = $this->_posModel->getPlaceById($storeId);

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($pos->getName());
        return $resultPage;
    }
}
