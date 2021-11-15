<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Block\Adminhtml\Sales\Order\Items\Renderer;

class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer
{
    protected $_posFactory = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Wyomind\PointOfSale\Model\PointOfSaleFactory $posFactory, \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry, \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration, \Magento\Framework\Registry $registry, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
        $this->_posFactory = $posFactory;
    }
    public function getPos()
    {
        $data = [];
        $places = $this->_posFactory->create()->getCollection();
        foreach ($places as $pos) {
            if ($this->_permissionsHelper->isAllowed($pos->getId())) {
                $data[] = $pos;
            }
        }
        return $data;
    }
}