<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

use Magento\Framework\App\Filesystem\DirectoryList;
use Aitoc\Gifts\Controller\RegistryConstants;

class ExportCouponsCsv extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context, $ruleFactory, $registry, $resultPageFactory);
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export coupon codes as CSV file
     *
     * @return \Magento\Framework\App\ResponseInterface|null
     */
    public function execute()
    {
        $rule = $this->getRule();
        if ($rule->getId()) {
            $fileName = RegistryConstants::AITOC_GIFTS_COUPON_FILENAME  . '.csv';
            $content = $this->_view->getLayout()->createBlock(
                \Aitoc\Gifts\Block\Adminhtml\Rule\Edit\Tab\Coupons\Grid::class
            )->getCsvFile();

            return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
        } else {
            return $this->_redirect('aitoc_gifts/rule/edit', ['_current' => true]);
        }
    }
}
