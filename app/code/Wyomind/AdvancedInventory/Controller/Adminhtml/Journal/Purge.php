<?php

/*
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Controller\Adminhtml\Journal;

class Purge extends \Wyomind\AdvancedInventory\Controller\Adminhtml\Journal
{
    
    protected $_filter = null;
    protected $_collectionFactory = null;

    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Wyomind\AdvancedInventory\Model\Journal $journalModel,
            \Magento\Ui\Component\MassAction\Filter $filter,
            \Wyomind\AdvancedInventory\Model\ResourceModel\Journal\CollectionFactory $collectionFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $resultPageFactory, $journalModel);
    }

    public function execute()
    {

        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        try {
            foreach ($collection as $row) {
                $row->delete();
            }
            $this->messageManager->addSuccess(__('Journal deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('advancedinventory/journal/index');
    }

}
