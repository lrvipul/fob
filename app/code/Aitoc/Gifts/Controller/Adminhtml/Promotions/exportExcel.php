<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Promotions;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportExcel extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->correctPeriod();
        $fileName = 'promotions_total.xml';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }

    private function correctPeriod()
    {
        $filter = $this->getRequest()->getParam('filter');
        $filter = base64_decode($filter);
        $data = [];
        parse_str(urldecode($filter), $data);

        if (isset($data['report_period'])) {
            $data['report_period'] = preg_replace("/[^a-zA-Z]+/", "", $data['report_period']);
        }

        $filter = http_build_query($data);
        $filter = base64_encode($filter);
        $this->getRequest()->setParam('filter', $filter);
    }

    /**
     * Acl admin user check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aitoc_Gifts::analytics');
    }
}