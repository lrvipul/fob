<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml;

/**
 * Class Attributes
 * @package Wyomind\PointOfSale\Controller\Adminhtml
 */
abstract class Attributes extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $_dataPersistor;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;
    /**
     * @var \Wyomind\PointOfSale\Model\AttributesFactory
     */
    protected $_attributesModelFactory;
    /**
     * @var \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory
     */
    protected $_attributesCollectionFactory;

    /**
     * Attributes constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Wyomind\PointOfSale\Model\AttributesFactory $attributesModelFactory
     * @param \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory $attributesCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Wyomind\PointOfSale\Model\AttributesFactory $attributesModelFactory,
        \Wyomind\PointOfSale\Model\ResourceModel\Attributes\CollectionFactory $attributesCollectionFactory
    )
    {
        parent::__construct($context);
        $this->_dataPersistor = $dataPersistor;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_filter = $filter;
        $this->_attributesModelFactory = $attributesModelFactory;
        $this->_attributesCollectionFactory = $attributesCollectionFactory;
    }

    /**
     * @param $title
     * @return $this
     */
    protected function _initAction($title)
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::sales');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Point Of Sale > ') . $title);

        return $this;
    }
}