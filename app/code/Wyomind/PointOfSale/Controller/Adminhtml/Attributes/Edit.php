<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml\Attributes;

/**
 * Class Edit
 * @package Wyomind\PointOfSale\Controller\Adminhtml\Attributes
 */
class Edit extends \Wyomind\PointOfSale\Controller\Adminhtml\Attributes
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $attribute = $this->_attributesModelFactory->create();

        if ($id) {
            $attribute->load($id);
            if (!$attribute->getAttributeId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $this->_redirect("pointofsale/attributes/index");
                return;
            }
        }

        $this->_coreRegistry->register('attribute', $attribute);

        $title = $attribute->getId() ? __('Edit Attribute: ') . $attribute->getCode() : __('New Attribute');
        $this->_initAction($title);
        $this->_view->renderLayout();
    }
}