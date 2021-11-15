<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab;

/**
 * Class Additional
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab
 */
class Additional extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public function __construct(\Wyomind\PointOfSale\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('pointofsale');
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('pickupatstore', ['legend' => __('Additional Information')]);
        $wysiwygConfig = $this->wysiwygConfig->getConfig();
        $wysiwygConfig->setData('add_widgets', false);
        foreach ($this->attributesCollection as $attribute) {
            if ($attribute->getType() == 1) {
                $fieldset->addField($attribute->getCode(), 'editor', ['label' => __($attribute->getLabel()), 'name' => $attribute->getCode(), 'config' => $wysiwygConfig]);
                ///**
                // * @var \Magento\Store\Model\StoreManagerInterface
                // */
                //protected $_storeManager;
                //
                ///**
                // * @var \Magento\Cms\Model\Template\FilterProvider
                // */
                //protected $_filterProvider;
                //
                //
                //$content = $model->getData('content');
                //return $this->_filterProvider->getBlockFilter()
                //    ->setStoreId($this->_storeManager->getStore()->getId())
                //    ->filter($content);
            } elseif ($attribute->getType() == 0) {
                $fieldset->addField($attribute->getCode(), 'textarea', ['label' => __($attribute->getLabel()), 'name' => $attribute->getCode()]);
            } elseif ($attribute->getType() == 2) {
                $fieldset->addField($attribute->getCode(), 'text', ['label' => __($attribute->getLabel()), 'name' => $attribute->getCode()]);
            }
        }
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Additional information');
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Additional information');
    }
    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}