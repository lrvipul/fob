<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Controller\Adminhtml\Journal;

/**
 * Export to csv mass action
 * @since 6.5.0
 */
class ExportCsv extends \Wyomind\AdvancedInventory\Controller\Adminhtml\Journal
{

    protected $_filter = null;
    protected $_collectionFactory = null;
    protected $_fileFactory = null;

    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Wyomind\AdvancedInventory\Model\Journal $journalModel,
            \Magento\Ui\Component\MassAction\Filter $filter,
            \Wyomind\AdvancedInventory\Model\ResourceModel\Journal\CollectionFactory $collectionFactory,
            \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $resultPageFactory, $journalModel);
    }

    public function execute()
    {
        
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        
//        $ids = $this->getRequest()->getParam('ids');
//        $collection = $this->_collectionFactory->create()->addFieldToFilter("id", ["in" => $ids]);
        // Write headers to the csv file
        $columns = ["id", "datetime", "user", "context", "action", "reference", "details"];
        $content = implode(";", $columns) . "\n";

        foreach ($collection->getItems() as $row) {
            $content .= implode(";", [
                        $row->getId(),
                        $row->getDatetime(),
                        $row->getUser(),
                        $row->getContext(),
                        $row->getAction(),
                        $row->getReference(),
                        $row->getDetails()
                    ]) . "\n";
        }
        // Return the CSV file
        $fileName = 'stock_movements.csv';
        return $this->_fileFactory->create(
                        $fileName, $content, \Magento\Framework\Filesystem\DirectoryList::SYS_TMP
        );
    }

}
