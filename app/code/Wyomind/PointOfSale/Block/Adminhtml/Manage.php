<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml;

/**
 *
 */
class Manage extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var
     */
    protected $_controller;
    /**
     * @var
     */
    protected $_blockGroup;
    /**
     * @var
     */
    protected $_headerText;
    /**
     * @var
     */
    protected $_addButtonLabel;
    /**
     * Manage constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }
    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function _construct()
    {
        $this->_controller = "adminhtml_manage";
        $this->_blockGroup = "Wyomind_PointOfSale";
        $this->_headerText = __("Points Of Sale / Warehouses");
        $this->_addButtonLabel = __("Create New Point Of Sale / Warehouse");
        $this->addButton("export", ["label" => __("Export a csv File"), "class" => "save", "onclick" => "setLocation('" . $this->getUrl('*/*/exportCsv') . "')"]);
        $this->addButton("import", ["label" => __("Import a csv File"), "class" => "save", "onclick" => 'require(["pos_index"], function(pos_index) {pos_index.importCsvModal();});']);
        parent::_construct();
    }
}