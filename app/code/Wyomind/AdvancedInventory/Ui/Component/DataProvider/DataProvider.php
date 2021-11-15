<?php

/**
 * Copyright Â© 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Ui\Component\DataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{

    protected $_helperPermissions = null;

    public function getSearchCriteria()
    {

        $object = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->_helperPermissions == null) {
            $this->_helperPermissions = $object->create("\Wyomind\AdvancedInventory\Helper\Permissions");
        }

        $session = $object->get('Magento\Backend\Model\Session');


        if ($session->getData('selected_ids')) {
            $value = new \Zend_Db_Expr($session->getData('selected_ids'));
            $this->addFilter(
                $this->filterBuilder->setField('entity_id')->setValue($value)->setConditionType('in')->create()
            );
        }

        if (!$this->searchCriteria) {
            $this->searchCriteria = $this->searchCriteriaBuilder->create();
        }

        if (!$this->_helperPermissions->hasAllPermissions()) {

            $filterGroupBuilder = $object->create("\Magento\Framework\Api\Search\FilterGroupBuilder");


            if (!count($this->_helperPermissions->getUserPermissions())) {
                $filterGroupBuilder->addFilter($this->filterBuilder->setField("assigned_to")->setValue(0)->setConditionType('finset')->create());
            } else {
                $filterGroupBuilder->addFilter(
                    $this->filterBuilder->setField("assigned_to")
                        ->setValue("(,|^)(" . implode("|", $this->_helperPermissions->getUserPermissions()) . ")(,|$)")
                        ->setConditionType('regexp')->create()
                );
            }

            $filters = $filterGroupBuilder->create();

            $this->searchCriteria->setFilterGroups(array_merge($this->searchCriteria->getFilterGroups(), [$filters]));

        }
        $this->searchCriteria->setRequestName($this->name);
        return $this->searchCriteria;


    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == "assigned_to") {
            $filter = $this->filterBuilder->setField($filter->getField())->setValue($filter->getValue())->setConditionType('finset')->create();
        }
        if (in_array($filter->getField(),["created_at","base_grand_total","grand_total","increment_id", "billing_name","shipping_name"])) {
            $filter->setField("main_table." . $filter->getField());
        }

        $this->searchCriteriaBuilder->addFilter($filter);
    }
}
