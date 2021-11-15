<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

use Magento\Framework\View\LayoutFactory;

class CouponsGrid extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $ruleFactory, $registry, $resultPageFactory);
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Coupon codes grid
     *
     * @return void
     */
    public function execute()
    {
        $this->getRule();
        $layout = $this->layoutFactory->create();
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultRaw->setContents(
            $layout->createBlock(\Aitoc\Gifts\Block\Adminhtml\Rule\Edit\Tab\Coupons\Grid::class)->toHtml()
        );

        return $resultRaw;
    }
}
