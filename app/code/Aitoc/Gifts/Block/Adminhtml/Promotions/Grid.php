<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Promotions;

use Magento\Backend\Block\Widget\Grid\Extended;

class Grid extends Extended
{
    /**
     * @var string
     */
    protected $_template = 'Aitoc_Gifts::promotions/grid.phtml';

    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Website\OptionHash
     */
    private $storesOptionHash;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesno;

    /**
     * @var int
     */
    protected $_defaultLimit = 10;

    /**
     * @var int
     */
    protected $_defaultPage = 1;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_pagerVisibility = false;
        $this->_filterVisibility = false;
        $this->_emptyText = __('We couldn\'t find any records.');
    }

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                  $context
     * @param \Magento\Backend\Helper\Data                             $backendHelper
     * @param \Aitoc\Gifts\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magento\Config\Model\Config\Source\Website\OptionHash   $storesOptionHash
     * @param \Magento\Config\Model\Config\Source\Yesno                $yesno
     * @param array                                                    $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Aitoc\Gifts\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Config\Model\Config\Source\Website\OptionHash $storesOptionHash,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->storesOptionHash = $storesOptionHash;
        $this->yesno = $yesno;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->ruleCollectionFactory->create()
            ->addFieldToFilter('action', ['eq' => $this->getData('action')])->setOrder('priority', 'ASC');
        $this->unsetData('action');
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'from_date',
            [
                'header' => __('From Date'),
                'index' => 'from_date',
                'sortable' => false,
                'type' => 'date',
                'timezone' => false,
                'column_css_class' => 'col-date',
                'header_css_class' => 'col-date'
            ]
        );

        $this->addColumn(
            'to_date',
            [
                'header' => __('To Date'),
                'index' => 'to_date',
                'sortable' => false,
                'type' => 'date',
                'timezone' => false,
                'column_css_class' => 'col-date',
                'header_css_class' => 'col-date'
            ]
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'stores',
                [
                    'header'   => __('Store View'),
                    'index'    => 'stores',
                    'type' => 'store',
                    'store_all' => true,
                    'store_view' => true,
                    'sortable' => false,
                ]
            );
        }

        $this->addColumn(
            'coupon_type',
            [
                'header' => __('Coupon Type'),
                'index' => 'coupon_type',
                'sortable' => false
            ]
        );
        $this->addColumn(
            'coupon_code',
            [
                'header' => __('Coupon Code'),
                'index' => 'coupon_code',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'discount_type',
            [
                'header' => __('Discount Type'),
                'index' => 'discount_amount',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'discount_amount',
            [
                'header' => __('Discount Amount'),
                'index' => 'discount_amount',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'cart_amount',
            [
                'header' => __('Cart Amount'),
                'index' => 'cart_amount',
                'sortable' => false
            ]
        );


        $this->addColumn(
            'stop_rules_processing',
            [
                'header' => __('Stop Rule Processing'),
                'index' => 'stop_rules_processing',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'sortable' => false,
                'type' => 'date',
                'timezone' => false,
                'column_css_class' => 'col-date',
                'header_css_class' => 'col-date'
            ]
        );

        $this->addColumn(
            'activity',
            [
                'header' => __('Activity'),
                'sortable' => false
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => '',
                'sortable' => false
            ]
        );
        $this->prepareColumnRederers();

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    private function prepareColumnRederers()
    {
        $couponTypeColumn = $this->getColumn('coupon_type');
        $renderer = $this->getLayout()->createBlock(
            'Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer\CouponType'
        )->setColumn($couponTypeColumn);
        $couponTypeColumn->setRenderer($renderer);

        $discountAmountColumn = $this->getColumn('discount_amount');
        $renderer = $this->getLayout()->createBlock(
            'Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer\DiscountAmount'
        )->setColumn($discountAmountColumn);
        $discountAmountColumn->setRenderer($renderer);

        $cartAmountColumn = $this->getColumn('cart_amount');
        $renderer = $this->getLayout()->createBlock(
            'Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer\CartAmount'
        )->setColumn($cartAmountColumn);
        $cartAmountColumn->setRenderer($renderer);

        $discountTypeColumn = $this->getColumn('discount_type');
        $renderer = $this->getLayout()->createBlock(
            'Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer\DiscountType'
        )->setColumn($discountTypeColumn);
        $discountTypeColumn->setRenderer($renderer);

        $activityColumn = $this->getColumn('activity');
        $renderer = $this->getLayout()->createBlock(
            'Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer\Activity'
        )->setColumn($activityColumn);
        $activityColumn->setRenderer($renderer);

        $actionColumn = $this->getColumn('actions');
        $renderer = $this->getLayout()->createBlock(
            'Aitoc\Gifts\Block\Adminhtml\Promotions\Grid\Renderer\Delete'
        )->setColumn($actionColumn);
        $actionColumn->setRenderer($renderer);

        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function isRowDisabled($row)
    {
        return !$row->getData('is_active');
    }

    /**
     * Return grid row url
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        $ruleUrl = $this->getUrl(
            'aitoc_gifts/rule/edit',
            ['rule_id' => $row->getRuleId()]);

        return $row->getIsActive() ? $ruleUrl : '#';
    }
}
