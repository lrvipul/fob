<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\AdvancedInventory\Model\System\Config\Source;

class Assignation
{
    protected $_posCollection = null;
    public function __construct(\Wyomind\AdvancedInventory\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        if ($this->request->getParam('store')) {
            $this->_posCollection = $this->posCollection->getPlacesByStoreId($this->request->getParam('store'), null);
        } elseif ($this->request->getParam('website')) {
            $website = $this->storeManager->getWebsite($this->request->getParam('website'));
            $stores = $website->getStoreCollection();
            $where = [];
            foreach ($stores as $store) {
                $where[] = "FIND_IN_SET(" . $store->getId() . ",main_table.store_id)";
            }
            $this->_posCollection = $this->posCollection;
            $this->_posCollection->getSelect()->where(implode(" OR ", $where));
        } else {
            $this->_posCollection = $this->posCollection;
        }
    }
    public function toOptionArray()
    {
        $data = [['value' => -2, 'label' => 'Automatic']];
        $data[] = ['value' => -1, 'label' => 'No Assignation'];
        foreach ($this->_posCollection as $pos) {
            $data[] = ['value' => $pos->getPlaceId(), 'label' => "[" . $pos->getStoreCode() . "] " . $pos->getName()];
        }
        return $data;
    }
}