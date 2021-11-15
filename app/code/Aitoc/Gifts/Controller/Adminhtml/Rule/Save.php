<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

class Save extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * @var \Aitoc\Gifts\Model\Date
     */
    private $date;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $salesRuleFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aitoc\Gifts\Model\Date $date,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory
    ) {
        parent::__construct($context, $ruleFactory, $registry, $resultPageFactory);
        $this->date = $date;
        $this->salesRuleFactory = $salesRuleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);

        if ($data = $this->getRequest()->getParams()) {
            $rule = $this->getRule();
            try {
                $data = $this->prepareData($data, $rule);
                $rule->addData($data);
                $rule->getResource()->save($rule);
                $this->messageManager->addSuccessMessage(__('Rule has been successfully saved'));

                if ($redirectBack == 'edit') {
                    return  $this->_redirect('*/promotions/index');
                } elseif ($redirectBack == 'continue') {
                    return $this->_redirect('*/*/edit', ['rule_id' => $rule->getId()]);
                } elseif ($redirectBack == 'duplicate') {
                    $newRule = $this->ruleFactory->create();
                    $newRule->setData($rule->getData());
                    $newRule->unsetData('rule_id');
                    $newRule->getResource()->save($newRule);
                    $this->messageManager->addSuccessMessage(__('You duplicated the rule.'));

                    return $this->_redirect('*/*/edit', ['rule_id' => $newRule->getId()]);
                } elseif ($redirectBack === 'new') {
                    return $this->_redirect('*/*/edit');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->_redirect('*/*/edit', ['rule_id' => $rule->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(self::DEFAULT_ERROR_MESSAGE));

                return $this->_redirect('*/*/edit', ['rule_id' => $rule->getId()]);
            }
        }

        $this->messageManager->addErrorMessage(__('Unable to save the rule'));

        return $this->redirectIndex();
    }

    /**
     * @param array                    $data
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return array
     */
    private function prepareData(array $data, \Aitoc\Gifts\Model\Rule $rule)
    {
        $data = $this->prepareConditions($data, $rule);
        $data = $this->prepareStoresAndCustomerGroup($data);
        $data = $this->prepareProductSkus($data);
        $data = $this->prepareCouponData($data);
        $data = $this->removeUnusedFields($data);
        $data = $this->prepareDate($data);

        return $data;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private function prepareDate($data)
    {
        $data['created_at'] = $this->date->getCurrentDate();
        $data['from_date'] = $data['from_date'] ? $this->date->getDateFromString($data['from_date'], false) : '';
        $data['to_date'] = $data['to_date'] ? $this->date->getDateFromString($data['to_date'], false) : '';

        return $data;
    }

    /**
     * @param array                    $data
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return array
     */
    private function prepareConditions(array $data, \Aitoc\Gifts\Model\Rule $rule)
    {
        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];

            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
            }

            $salesRule = $rule->getSalesRule();
            /** @var \Magento\SalesRule\Model\Rule $salesRule */
            $data['conditions_serialized'] = $salesRule->loadPost($data)->beforeSave()->getConditionsSerialized();
            $data['actions_serialized']    = $salesRule->loadPost($data)->beforeSave()->getActionsSerialized();

            unset($data['rule'], $data['conditions'], $data['actions']);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareStoresAndCustomerGroup(array $data)
    {
        if (is_array($data['stores'])) {
            $data['stores'] = implode(',', $data['stores']);
        }

        if (is_array($data['customer_group'])) {
            $data['customer_group'] = implode(',', $data['customer_group']);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareProductSkus(array $data)
    {
        if (isset($data['links'])
            && isset($data['links']['product_skus'])
            && is_array($data['links']['product_skus'])
        ) {
            $skus = [];
            foreach ($data['links']['product_skus'] as $product) {
                $skus[] = $product['sku'];
            }

            $data['product_skus'] = implode(',', $skus);
            unset($data['links'], $data['product_skus_product_listing']);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareCouponData(array $data)
    {
        $data['use_auto_generation'] = (int)((isset($data['coupon_type']) && $data['coupon_type'] == 'generator')
            ? 1 : 0);

        if (isset($data['sales_rule_id']) && $data['sales_rule_id']) {
            $ruleModel = $this->salesRuleFactory->create()->load($data['sales_rule_id']);
            $coupons = $ruleModel->getCoupons();
            $data['coupon_code'] = $coupons ? $coupons[array_rand($coupons)]->getCode() : $ruleModel->getCouponCode();
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function removeUnusedFields(array $data)
    {
        if (isset($data['rule_id'])) {
            unset($data['rule_id']);
        }

        if (isset($data['key'])) {
            unset($data['key']);
        }

        if (isset($data['back'])) {
            unset($data['back']);
        }

        if (isset($data['form_key'])) {
            unset($data['form_key']);
        }

        return $data;
    }
}
