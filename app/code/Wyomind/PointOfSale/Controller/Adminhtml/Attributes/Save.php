<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PointOfSale\Controller\Adminhtml\Attributes;

/**
 * Class Save
 * @package Wyomind\PointOfSale\Controller\Adminhtml\Attributes
 */
class Save extends \Wyomind\PointOfSale\Controller\Adminhtml\Attributes
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        if ($data) {
            $attribute = $this->_attributesModelFactory->create();
            $id = $this->getRequest()->getParam('attribute_id');
            
            if ($id) {
                $attribute->load($id);
            }
            
            if (empty($data['attribute_id'])) {
                $data['attribute_id'] = null;
            }
            
            try {
                foreach ($data as $index => $value) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $attribute->setData($index, $value);
                }
                
                $this->_dataPersistor->set('attribute', $data);

                
                $attribute->save();
                
                $this->messageManager->addSuccess(__('The attribute has been saved.'));
                $this->_dataPersistor->clear('attribute');
                
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect("pointofsale/attributes/edit", ['attribute_id' => $attribute->getId()]);
                    return;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to save the attribute. '.$e->getMessage()));
                $this->_redirect("pointofsale/attributes/edit", ['attribute_id' => $id]);
                return;
            }
        }
        
        $this->_redirect("pointofsale/attributes/index");
    }
}