<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model;

class SalesRule extends \Magento\SalesRule\Model\Rule
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Aitoc\Gifts\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    public function validateRule(\Magento\Quote\Model\Quote $quote)
    {
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        return $this->getConditions()->validate($address);
    }

    /**
     * @param $item
     *
     * @return boolean
     */
    public function validateActions($item)
    {
        return $this->getActions()->validate($item);
    }
}