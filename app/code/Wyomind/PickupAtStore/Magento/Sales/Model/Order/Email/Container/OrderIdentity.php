<?php

namespace Wyomind\PickupAtStore\Magento\Sales\Model\Order\Email\Container;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Wyomind\Framework\Helper\Module;
class OrderIdentity extends \Magento\Sales\Model\Order\Email\Container\OrderIdentity
{
    /**
     * @var null
     */
    protected $pickupStore = null;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($scopeConfig, $storeManager);
    }
    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $list = parent::getEmailCopyTo();
        $copyToSource = $this->framework->getStoreConfig("carriers/pickupatstore/emails/copy_to_pos", $this->getStore()->getId());
        if ($copyToSource && !empty($this->pickupStore) && $this->pickupStore->getEmail() != "") {
            $pickupStoreEmail = [$this->pickupStore->getEmail()];
            if (!$list) {
                return $pickupStoreEmail;
            } else {
                return array_merge($list, $pickupStoreEmail);
            }
        }
        return $list;
    }
    public function setPickupStore($pos)
    {
        $this->pickupStore = $pos;
    }
}