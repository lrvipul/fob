<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Block;

/**
 * Block defining the first step of the shipping step in the checkout
 * Allow to choice if the customer wants to pickup in store his order or not
 */
class Choice extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Wyomind_PickupAtStore::choice.phtml';
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, \Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
        $this->setTemplate($this->_template);
    }
    /**
     * Get the title of the block (displayed on the frontend)
     * @return string the title of the block
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTitle()
    {
        return $this->_config->getChoiceTitle();
    }
    public function getStorePickupActivatedDefault()
    {
        return $this->_config->getStorePickupActivatedDefault();
    }
    public function toHtml()
    {
        if ($this->_config->getDisplayChoiceBlock()) {
            return parent::toHtml();
        } else {
            return "";
        }
    }
}