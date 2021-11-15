<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml\Manage;

/**
 * Edit action
 */
class State extends \Wyomind\PointOfSale\Controller\Adminhtml\PointOfSale
{
    /**
     * Execute action
     * @return void
     */
    public function execute()
    {
        $countryCode = $this->getRequest()->getParam('country');
        $states = [];
        $states[] = "<option value=''>Please Select</option>";
        if ($countryCode != '') {
            $statesCollection = $this->_regionCollection->addCountryFilter($countryCode)->load();
            foreach ($statesCollection as $_state) {
                $states[] = "<option value='" . $_state->getCode() . "'>" . $_state->getDefaultName() . "</option>";
            }
        }
        $resultRaw = $this->_resultRawFactory->create();

        if (count($states) == 1) {
            return $resultRaw->setContents("<option value=''>------</option>");
        } else {
            return $resultRaw->setContents(implode(' ', $states));
        }
    }
}