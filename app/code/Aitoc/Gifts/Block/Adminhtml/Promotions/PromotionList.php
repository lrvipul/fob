<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */

namespace Aitoc\Gifts\Block\Adminhtml\Promotions;

class PromotionList extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Aitoc\Gifts\Model\Pool\Action
     */
    private $actionPool;

    /**
     * PromotionList constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aitoc\Gifts\Model\Pool\Action         $actionPool
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aitoc\Gifts\Model\Pool\Action $actionPool,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->actionPool = $actionPool;

    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChildGridsByAction();

        return $this;
    }

    /**
     * @return $this
     */
    protected function setChildGridsByAction()
    {
        foreach ($this->getActions() as $code => $action) {
            $this->addChild(
                'child_grid_' . $code,
                'Aitoc\Gifts\Block\Adminhtml\Promotions\Grid'
            )->setData('action', $code);
        }

        return $this;
    }

    /**
     * @return \Aitoc\Gifts\Model\Pool\AbstractEvent[]
     */
    public function getActions()
    {
        return $this->actionPool->getActions();
    }

    /**
     * @param $code
     *
     * @return string
     */
    public function getGridHtml($code)
    {
        return $this->getChildHtml('child_grid_' . $code);
    }
}
