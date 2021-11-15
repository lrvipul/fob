<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Plugin\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Aitoc\Gifts\Controller\RegistryConstants;
use Aitoc\Gifts\Model\Rule\Type\DiscountType;

class Item
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Aitoc\Gifts\Model\Manager\ItemManager
     */
    private $itemManager;

    /**
     * @var \Aitoc\Gifts\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Aitoc\Gifts\Model\Resolver\ActionResolver
     */
    private $actionResolver;

    /**
     * @var array
     */
    private $priceFields = [
        'price',
        'base_price',
        'custom_price',
        'original_custom_price',
        'price_incl_tax',
        'base_price_incl_tax',
        'row_total',
        'row_total_incl_tax',
        'base_row_total',
        'base_row_total_incl_tax',
    ];

    /**
     * @var \Aitoc\Gifts\Model\Manager\RuleManager
     */
    private $ruleManager;

    /**
     * @var \Aitoc\Gifts\Model\Manager\DiscountManager
     */
    private $discountManager;

    /**
     * Item constructor.
     *
     * @param \Aitoc\Gifts\Model\Manager\ItemManager            $itemManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Aitoc\Gifts\Model\RuleFactory                    $ruleFactory
     * @param \Aitoc\Gifts\Model\Resolver\ActionResolver        $actionResolver
     * @param \Aitoc\Gifts\Model\Manager\RuleManager            $ruleManager
     * @param \Aitoc\Gifts\Model\Manager\DiscountManager        $discountManager
     */
    public function __construct(
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Aitoc\Gifts\Model\Resolver\ActionResolver $actionResolver,
        \Aitoc\Gifts\Model\Manager\RuleManager $ruleManager,
        \Aitoc\Gifts\Model\Manager\DiscountManager $discountManager
    ) {
        $this->scopeConfig    = $scopeConfig;
        $this->itemManager    = $itemManager;
        $this->ruleFactory    = $ruleFactory;
        $this->actionResolver = $actionResolver;
        $this->ruleManager = $ruleManager;
        $this->discountManager = $discountManager;
    }

    public function aroundRepresentProduct(
        AbstractItem $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($proceed($product)) {
            $productRuleId = $product->getData(RegistryConstants::CURRENT_RULE_ID);
            $itemRuleId    = $this->itemManager->getRuleIdFromItem($subject);

            return $productRuleId === $itemRuleId;
        } else {
            return false;
        }
    }

    /**
     * @param AbstractItem $subject
     * @param              $key
     * @param null         $value
     *
     * @return array
     */
    public function beforeSetData(AbstractItem $subject, $key, $value = null)
    {
        if (!is_string($key)) {
            return [$key, $value];
        }

        if (in_array($key, $this->priceFields)) {
            $ruleId = $this->itemManager->getRuleIdFromItem($subject);
            $rule   = $this->getRuleModelById($ruleId);

            if ($this->itemManager->isGiftItem($subject)
                && $rule->getId() && $this->isFullDiscount($rule)
            ) {
                return [$key, 0];
            }
        }

        return [$key, $value];
    }

    /**
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return boolean
     */
    private function isFullDiscount($rule)
    {
        switch ($this->ruleManager->getDiscountType($rule)) {
            case DiscountType::DISCOUNT_AMOUNT_TYPE:
                return $this->ruleManager->getDiscountAmount($rule) == 0;
                break;
            case DiscountType::DISCOUNT_PERCENT_TYPE:
                return
                    $this->discountManager->validatePercentValue($this->ruleManager->getDiscountPercent($rule)) == 100;
                break;
        }

        return false;
    }

    /**
     * @param $ruleId
     *
     * @return mixed
     */
    private function getRuleModelById($ruleId)
    {
        return $this->ruleFactory->create()->getRuleById($ruleId);
    }
}
