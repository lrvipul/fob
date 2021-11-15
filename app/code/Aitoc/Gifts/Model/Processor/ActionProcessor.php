<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Processor;

class ActionProcessor implements ProcessorInterface
{
    /**
     * @var \Aitoc\Gifts\Model\Rule\Manager\RuleManager
     */
    private $ruleManager;

    /**
     * @var \Aitoc\Gifts\Model\Validator\RuleValidator
     */
    private $ruleValidator;

    /**
     * @var \Aitoc\Gifts\Model\Resolver\ActionResolver
     */
    private $actionResolver;

    /**
     * @var \Aitoc\Gifts\Model\Manager\CartManager
     */
    private $cartManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    private $skipProccessorAction = [
        'checkout_cart_delete'
    ];

    /**
     * @var \Aitoc\Gifts\Model\Repository\StatisticRepository
     */
    private $statisticRepository;

    /**
     * @var \Aitoc\Gifts\Model\Manager\ItemManager
     */
    private $itemManager;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Repository
     */
    private $repositoryItem;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Aitoc\Gifts\Model\Manager\RuleManager $ruleManager,
        \Aitoc\Gifts\Model\Validator\RuleValidator $ruleValidator,
        \Aitoc\Gifts\Model\Resolver\ActionResolver $actionResolver,
        \Aitoc\Gifts\Model\Manager\CartManager $cartManager,
        \Magento\Framework\App\Request\Http $request,
        \Aitoc\Gifts\Model\Repository\StatisticRepository $statisticRepository,
        \Aitoc\Gifts\Model\Manager\ItemManager $itemManager,
        \Magento\Quote\Model\Quote\Item\Repository $repositoryItem,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->ruleManager = $ruleManager;
        $this->ruleValidator = $ruleValidator;
        $this->actionResolver = $actionResolver;
        $this->cartManager = $cartManager;
        $this->request = $request;
        $this->statisticRepository = $statisticRepository;
        $this->itemManager = $itemManager;
        $this->repositoryItem = $repositoryItem;
        $this->cartRepository = $cartRepository;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($quote)
    {
        $this->doProcess($quote, $this->ruleManager->getAvailableRules($quote));

        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function processCoupon($quote, $couponCode)
    {
        $rulesByCoupon = $this->ruleManager->getAvailableRuleByCoupon($quote, $couponCode);
        $rulesNoCoupon = $this->ruleManager->getAvailableRules($quote);
        $this->doProcess($quote, array_merge($rulesByCoupon, $rulesNoCoupon));

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param $rules
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function doProcess($quote, $rules)
    {
        if ($rules) {
            if (!$this->isSkipProcessor($quote)) {
                $this->prepareProcess($quote);

                foreach ($rules as $rule) {
                    if ($this->cartManager->ruleIsAlreadyApplied($rule->getId(), $quote)) {
                        if ($this->ruleValidator->isRuleStop($rule)) {
                            break;
                        } else {
                            continue;
                        }
                    }

                    /** @var \Aitoc\Gifts\Model\Rule\Action\ActionInterface $actionProcessorInstance */
                    $actionProcessorInstance = $this->actionResolver->resolve($rule->getAction());
                    $actionProcessorInstance->actionProcess($quote, $rule);
                    $this->cartManager->setAppliedRules($rule, $quote);

                    if ($this->ruleValidator->isRuleStop($rule)) {
                        break;
                    }
                }
            }

            $this->applyDiscount($rules, $quote);
        } else {
            if ($this->request->getFullActionName() == 'checkout_cart_couponPost'
                || ($this->request->isAjax() && $this->request->getActionName() != 'removeItem'))
            {
                $this->prepareProcess($quote);
            } else {
                $this->checkFlushQuote($quote);
            }
        }

        return $quote;
    }

    /**
     * @param $rules
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return $this
     */
    public function applyDiscount($rules, $quote)
    {
        foreach ($rules as $rule) {
            /** @var \Aitoc\Gifts\Model\Rule\Action\ActionInterface $actionProcessorInstance */
            $actionProcessorInstance = $this->actionResolver->resolve($rule->getAction());
            $actionProcessorInstance->calculateAndApplyDiscount($rule, $quote);
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    public function isSkipProcessor($quote)
    {
        $isSkip = in_array($this->request->getFullActionName(), $this->skipProccessorAction);

        if ($isSkip && $this->request->getActionName() == 'delete') {
            if(!$this->checkFlushQuote($quote)) {
                $isSkip = false;
            }
        }

        return $isSkip;
    }

    /**
     * @param $quote
     * @return bool
     */
    private function checkFlushQuote($quote)
    {
        $result = false;
        $nonGiftItems = $this->cartManager->getAllNonGiftItems($quote);

        if (!$nonGiftItems) {
            $this->cartManager->flushQuote($quote);

            $result = true;
        }

        $deletedItems = $this->cartManager->getAllDeletedItems($quote, false);

        if ($deletedItems) {
            foreach ($deletedItems as $item) {
                if ($this->itemManager->isGiftItem($item)) {
                    $result = true;
                }
            }
        }

        $this->statisticRepository->updateProductInfoAfterDelete($quote);

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function prepareProcess($quote)
    {
        $giftItemsInCart = $this->statisticRepository->getGiftItemInQuoteFromStatistic($quote);

        if ($giftItemsInCart && $quote->getAllItems()) {
                /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                if ($this->itemManager->isGiftItem($item)) {
                    $quote->removeItem($item->getItemId());
                }
            }
        }

        $this->cartManager->collectDeletedItems($quote);

        return $quote;
    }
}
