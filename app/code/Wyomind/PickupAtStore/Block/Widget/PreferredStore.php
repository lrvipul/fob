<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 24/01/2019
 * Time: 16:02
 */

namespace Wyomind\PickupAtStore\Block\Widget;


class PreferredStore extends \Magento\Framework\View\Element\Template
{
    protected $_template = "preferred-store.phtml";

    public function getDisplay()
    {
        if (!$this->hasData('display')) {
            $this->setData('display', 3);
        }
        return $this->getData('display');
    }

    public function getAutomatic() {
        if (!$this->hasData('automatic')) {
            $this->setData('automatic', 0);
        }
        return $this->getData('automatic');
    }



}