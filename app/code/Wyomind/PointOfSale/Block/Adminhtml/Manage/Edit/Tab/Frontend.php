<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab;

/**
 * Class Frontend
 * @package Wyomind\PointOfSale\Block\Adminhtml\Manage\Edit\Tab
 */
class Frontend extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $fieldset = $form->addFieldset('frontend_storelocator', ['legend' => __('Store Locator')]);
        $fieldset->addField('store_locator_description_use_global', 'select', ['name' => 'store_locator_description_use_global', 'label' => __('Store locator description template'), 'title' => __('Store locator description template'), 'options' => [1 => __('Use the global description template'), 0 => __('Use this POS/WH description template')]]);
        $fieldset->addField('store_locator_description', 'textarea', ['name' => 'store_locator_description', 'label' => __('Description template'), 'title' => __('Description template'), 'note' => 'Html and css code are supported. ' . '<br/>Available variables: {{code}}, {{name}}, {{phone}}, {{email}}, {{address_1}}, {{address_2}}, {{city}}, {{state}}, {{country}}, {{zipcode}}, {{hours}}, {{description}}, {{image}}, {{link}}' . '<br/>And all custom attributes configured: {{additional_attribute_code}}']);
        $fieldset = $form->addFieldset('frontend_storepage', ['legend' => __('Store Page')]);
        $fieldset->addField('store_page_enabled', 'select', ['name' => 'store_page_enabled', 'label' => __('Enable store page'), 'values' => [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No')]]]);
        $fieldset->addField('store_page_url_key', 'text', ['name' => 'store_page_url_key', 'label' => __('Url key'), 'title' => __('Url key')]);
        $wysiwygConfig = $this->wysiwygConfig->getConfig();
        $wysiwygConfig->setData('add_widgets', false);
        $fieldset->addField('store_page_content', 'editor', ['name' => 'store_page_content', 'label' => __('Page content template'), 'title' => __('Page content template'), 'config' => $wysiwygConfig, 'note' => 'Html and css code are supported. ' . '<br/>Available variables : {{code}}, {{name}}, {{phone}}, {{email}}, {{address_1}}, {{address_2}}, {{city}}, {{state}}, {{country}}, {{zipcode}}, {{hours}}, {{description}}, {{image}}, {{link}}, {{google_map}}' . '<br/>And all custom attributes configured: {{additional_attribute_code}}']);
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\\Backend\\Block\\Widget\\Form\\Element\\Dependence')->addFieldMap('store_page_enabled', 'store_page_enabled')->addFieldMap('store_page_url_key', 'store_page_url_key')->addFieldMap('store_page_content', 'store_page_content')->addFieldMap('store_locator_description_use_global', 'store_locator_description_use_global')->addFieldMap('store_locator_description', 'store_locator_description')->addFieldDependence('store_page_url_key', 'store_page_enabled', 1)->addFieldDependence('store_page_content', 'store_page_enabled', 1)->addFieldDependence('store_locator_description', 'store_locator_description_use_global', 0));
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Frontend');
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Frontend');
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