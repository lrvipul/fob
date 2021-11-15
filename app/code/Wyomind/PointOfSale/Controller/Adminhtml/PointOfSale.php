<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml;

/**
 * Simple google shopping backend controller
 */
abstract class PointOfSale extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory|null
     */
    protected $_resultPageFactory = null;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection|null
     */
    protected $_regionCollection = null;
    /**
     * @var null|\Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection
     */
    protected $_posCollection = null;
    /**
     * @var null|\Wyomind\Framework\Helper\Download
     */
    protected $_framework = null;
    /**
     * @var null|\Wyomind\PointOfSale\Model\PointOfSaleFactory
     */
    protected $_posModelFactory = null;
    /**
     * @var null
     */
    protected $_uploader = null;
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|null
     */
    protected $_resultForwardFactory = null;
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|null
     */
    protected $_resultRawFactory = null;
    /**
     * @var \Magento\Framework\Filesystem|null
     */
    protected $_filesystem = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * PointOfSale constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection $posCollection
     * @param \Wyomind\PointOfSale\Model\PointOfSaleFactory $posModelFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Wyomind\Framework\Helper\Download $framework
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Framework\Registry $coreRegistry,
        \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection $posCollection,
        \Wyomind\PointOfSale\Model\PointOfSaleFactory $posModelFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Wyomind\Framework\Helper\Download $framework,
        \Magento\Framework\Filesystem $filesystem
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_regionCollection = $regionCollection;
        $this->_posCollection = $posCollection;
        $this->_framework = $framework;
        $this->_posModelFactory = $posModelFactory;
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_resultRawFactory = $resultRawFactory;
        $this->_filesystem = $filesystem;

        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_PointOfSale::pos');
    }

    /**
     * @param $data
     * @return bool
     */
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            /** @var $validatorCustomLayout \Magento\Core\Model\Layout\Update\Validator */
            $validatorCustomLayout = $this->_objectManager->create('Magento\Core\Model\Layout\Update\Validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            if (!empty($data['custom_layout_update_xml']) && !$validatorCustomLayout->isValid(
                    $data['custom_layout_update_xml']
                )
            ) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
        }
        return $errorNo;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    abstract public function execute();
}
