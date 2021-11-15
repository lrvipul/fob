<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Block\Adminhtml\Rule\Edit\Tab\Coupons;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Sales rule coupon
     *
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $_salesRuleCoupon = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\SalesRule\Helper\Coupon $salesRuleCoupon
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\SalesRule\Helper\Coupon $salesRuleCoupon,
        array $data = []
    ) {
        $this->_salesRuleCoupon = $salesRuleCoupon;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare coupon codes generation parameters form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        /**
         * @var \Magento\SalesRule\Helper\Coupon $couponHelper
         */
        $couponHelper = $this->_salesRuleCoupon;

        $model = $this->_coreRegistry->registry(\Aitoc\Gifts\Controller\RegistryConstants::CURRENT_RULE);
        $ruleId = $model->getId();

        $form->setHtmlIdPrefix('coupons_');
        $gridBlock = $this->getLayout()->getBlock('rule_generated_coupons_tab_grid');
        $gridBlockJsObject = '';

        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $fieldset = $form->addFieldset('information_fieldset', []);
        $fieldset->addClass('ignore-validate');

        $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id', 'value' => $ruleId]);

        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Coupon Qty'),
                'title' => __('Coupon Qty'),
                'required' => true,
                'class' => 'validate-digits validate-greater-than-zero'
            ]
        );

        $fieldset->addField(
            'length',
            'text',
            [
                'name' => 'length',
                'label' => __('Code Length'),
                'title' => __('Code Length'),
                'required' => true,
                'note' => __('Excluding prefix, suffix and separators.'),
                'value' => $couponHelper->getDefaultLength(),
                'class' => 'validate-digits validate-greater-than-zero'
            ]
        );

        $fieldset->addField(
            'format',
            'select',
            [
                'label' => __('Code Format'),
                'name' => 'format',
                'options' => $couponHelper->getFormatsList(),
                'required' => true,
                'value' => $couponHelper->getDefaultFormat()
            ]
        );

        $fieldset->addField(
            'dash',
            'text',
            [
                'name' => 'dash',
                'label' => __('Dash Every X Characters'),
                'title' => __('Dash Every X Characters'),
                'note' => __('If empty no separation.'),
                'value' => $couponHelper->getDefaultDashInterval(),
                'class' => 'validate-digits'
            ]
        );

        $idPrefix = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $fieldset->addField(
            'generate_button',
            'note',
            [
                'text' => $this->getButtonHtml(
                    __('Generate'),
                    "generateCouponCodes('{$idPrefix}' ,'{$generateUrl}', '{$gridBlockJsObject}')",
                    'generate'
                )
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve URL to Generate Action
     *
     * @return string
     */
    public function getGenerateUrl()
    {
        return $this->getUrl('*/*/generate');
    }
}
