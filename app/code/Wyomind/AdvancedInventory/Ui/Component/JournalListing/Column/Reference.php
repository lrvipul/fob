<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Ui\Component\JournalListing\Column;

class Reference extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind, \Magento\Framework\View\Element\UiComponent\ContextInterface $context, \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory, array $components = [], array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $title = [];
                foreach (explode(",", $item[$this->getData('name')]) as $ref) {
                    $ref = explode("#", $ref);
                    switch ($ref[0]) {
                        case "S":
                            $store = $this->_storeManager->getStore($ref[1]);
                            $group = $this->_storeManager->getGroup($store->getGroupId());
                            $website = $this->_storeManager->getWebsite($store->getWebsiteId());
                            $title[] = $website->getName() . " > " . $group->getName() . " > " . $store->getName();
                            break;
                        case "O":
                            $data = $this->_orderModel->load($ref[1])->getIncrementId();
                            $title[] = "Order #" . $data;
                            break;
                        case "P":
                            $data = $this->_productModel->load($ref[1])->getSku();
                            $title[] = "Sku : " . $data;
                            break;
                        case "W":
                            $data = $this->_posModel->load($ref[1])->getName();
                            $title[] = "WH/POS : " . $data;
                            break;
                    }
                }
                $item[$this->getData('name')] = "<span title=\"" . implode("
", $title) . "\">" . $item[$this->getData('name')] . "</span>";
            }
        }
        return $dataSource;
    }
}