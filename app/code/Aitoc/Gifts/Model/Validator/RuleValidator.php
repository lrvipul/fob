<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Validator;

class RuleValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($object, $quote)
    {
        return $this->validateRules($quote, $object);
    }

    /**
     * @param $rule
     *
     * @return mixed
     */
    public function isRuleStop($rule)
    {
        return $rule->getStopRulesProcessing();
    }

    /**
     * @param $quote
     * @param \Aitoc\Gifts\Model\Rule $rules
     *
     * @return array
     */
    private function validateRules($quote, $rules)
    {
        $availableRules = [];

        if ($rules) {
            /** @var \Aitoc\Gifts\Model\Rule $rule */
            foreach ($rules as $rule) {

                /** @var \Aitoc\Gifts\Model\SalesRule $salesRulesModel */
                $salesRulesModel = $rule->getSalesRule();

                if ($salesRulesModel->validateRule($quote)) {
                    $availableRules[$rule->getId()] = $rule;
                }
            }
        }

        return $availableRules;
    }
}
