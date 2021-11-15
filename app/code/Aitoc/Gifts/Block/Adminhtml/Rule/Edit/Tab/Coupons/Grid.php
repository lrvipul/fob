<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Block\Adminhtml\Rule\Edit\Tab\Coupons;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry = null;

    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory
     */
    private $ruleCoupon;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \Magento\Backend\Helper\Data                               $backendHelper
     * @param \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory $ruleCoupon
     * @param \Magento\Framework\Registry                                $coreRegistry
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Aitoc\Gifts\Model\ResourceModel\Coupon\CollectionFactory $ruleCoupon,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->ruleCoupon    = $ruleCoupon;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('couponCodesGrid');
        $this->setUseAjax(true);
    }

    /**
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->ruleCoupon
            ->create()
            ->addRuleToFilter($this->getRule());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'coupon_code',
            [
                'header' => __('Coupon Code'),
                'index'  => 'coupon_code',
            ]
        );

        $this->addColumn(
            'expiration_date',
            [
                'header' => __('Expiration Date'),
                'index'  => 'expiration_date',
                'type'   => 'datetime',
                'align'  => 'center',
                'width'  => '160',
            ]
        );


        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index'  => 'created_at',
                'type'   => 'datetime',
                'align'  => 'center',
                'width'  => '160',
            ]
        );


        $this->addExportType('aitoc_gifts/rule/exportCouponsCsv', __('CSV'));
        $this->addExportType('aitoc_gifts/rule/exportCouponsXml', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Configure grid mass actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('coupon_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'    => __('Delete'),
                'url'      => $this->getUrl('aitoc_gifts/rule/couponsMassDelete', ['_current' => true]),
                'confirm'  => __('Are you sure you want to delete the selected coupon(s)?'),
                'complete' => 'refreshCouponCodesGrid',
            ]
        );

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->_coreRegistry->registry(\Aitoc\Gifts\Controller\RegistryConstants::CURRENT_RULE);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('aitoc_gifts/rule/couponsGrid', ['_current' => true]);
    }
}
