<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Repository;

use Aitoc\Gifts\Api\StatisticRepositoryInterface;

class StatisticRepository implements StatisticRepositoryInterface
{
    /**
     * @var \Aitoc\Gifts\Model\StatisticFactory
     */
    private $statisticFactory;

    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Statistic
     */
    private $statisticResource;

    /**
     * @var \Aitoc\Gifts\Model\ResourceModel\Statistic\CollectionFactory
     */
    private $collection;

    /**
     * @var \Aitoc\Gifts\Model\Date
     */
    private $date;

    /**
     * @var \Aitoc\Gifts\Model\Manager\CartManager
     */
    private $cartManager;

    /**
     * @var array
     */
    private $newStatisticsToSave = [];

    public function __construct(
        \Aitoc\Gifts\Model\StatisticFactory $statisticFactory,
        \Aitoc\Gifts\Model\ResourceModel\Statistic $statisticResource,
        \Aitoc\Gifts\Model\ResourceModel\Statistic\CollectionFactory $collection,
        \Aitoc\Gifts\Model\Date $date,
        \Aitoc\Gifts\Model\Manager\CartManager $cartManager
    ) {
        $this->statisticFactory = $statisticFactory;
        $this->statisticResource = $statisticResource;
        $this->collection = $collection;
        $this->date = $date;
        $this->cartManager = $cartManager;
    }

    /**
     * @param $quoteId
     * @param $ruleId
     *
     * @return bool
     */
    public function getStatisticByQuoteAndRule($quoteId, $ruleId)
    {
        if ($quoteId && $ruleId) {
            $collection = $this->collection->create();
            $collection->addFieldToFilter(self::RULE_ID_FIELD_NAME, $ruleId)
                ->addFieldToFilter(self::QUOTE_ID_FIELD_NAME, $quoteId);

            return $collection->getSize() ? $collection->getFirstItem() : false;
        }

        return false;
    }

    /**
     * @param $quoteId
     *
     * @return \Aitoc\Gifts\Model\Statistic
     */
    public function getStatisticsByQuoteId($quoteId)
    {
        $collection = $this->collection->create();
        $collection->addFieldToFilter(self::QUOTE_ID_FIELD_NAME, $quoteId);

        return $collection->getSize() ? $collection->getItems() : false;
    }

    /**
     * @return \Aitoc\Gifts\Model\Statistic
     */
    public function createStatisticModel()
    {
        return $this->statisticFactory->create();
    }

    /**
     * @param \Aitoc\Gifts\Model\Statistic $statistic
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Aitoc\Gifts\Model\Rule $rule
     *
     * @return \Aitoc\Gifts\Model\Statistic
     */
    public function addNewStatisticData($statistic, $quote, $item, $rule)
    {
        $productInfo = [];
        if ($statistic->getId()) {
            $productInfo = json_decode($this->getProductInfo($statistic), true);
        } else {
            $statistic->setData([
                self::RULE_ID_FIELD_NAME => $rule->getId(),
                self::QUOTE_ID_FIELD_NAME => $quote,
                self::IS_GUEST_FIELD_NAME => (int)(!$quote->getCustomerId() && !$quote->getCustomerEmail()),
                self::CUSTOMER_ID_FIELD_NAME => $quote->getCustomerId(),
                self::CUSTOMER_EMAIL_FIELD_NAME => $quote->getCustomerEmail(),
                self::CREATED_AT_FIELD_NAME => $this->date->getCurrentDate(),
            ]);
        }

        $productInfo = $this->updateProductInfo($productInfo, $item);
        $statistic
            ->setGiftCount($this->updateGiftCountValue($productInfo))
            ->setProductSkus(json_encode($productInfo));

        if ($quote->getId()) {
            $statistic->setData(self::QUOTE_ID_FIELD_NAME, $quote->getId());
            $this->statisticResource->save($statistic);
        } else {
            $statistic->setData(self::QUOTE_ID_FIELD_NAME, $quote);
            $this->newStatisticsToSave[] = $statistic;
        }

        return $statistic;
    }

    /**
     * @return $this
     */
    public function saveNewStatisticData()
    {
        while ($this->newStatisticsToSave) {
            $statistic = array_pop($this->newStatisticsToSave);
            $statistic->setData(self::QUOTE_ID_FIELD_NAME, $statistic->getData(self::QUOTE_ID_FIELD_NAME)->getId());
            $this->statisticResource->save($statistic);
        }
        return $this;
    }

    /**
     * @param array $productInfo
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return array
     */
    private function updateProductInfo($productInfo, $item)
    {
        $isAlreadyAdded = false;
        if ($productInfo) {
            foreach ($productInfo as $product) {
                if ($product['sku'] == $item->getSku()) {
                    $isAlreadyAdded = true;
                    $product['qty'] = $item->getQty();
                }
            }
        }

        if (!$isAlreadyAdded) {
            $productInfo[] = [
                'sku' => $item->getSku(),
                'qty' => $item->getQty(),
            ];
        }

        return $productInfo;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function updateProductInfoAfterDelete($quote)
    {
        $statistics = $this->getStatisticsByQuoteId($quote->getId());
        $deleteItems = $this->cartManager->getAllDeletedItems($quote);

        if ($deleteItems && $statistics) {
            foreach ($statistics as $statistic) {
                $productInfo = json_decode($this->getProductInfo($statistic), true);

                if ($productInfo) {
                    $updatedProductInfo = [];
                    foreach ($productInfo as $product) {
                        if (isset($deleteItems[$product['sku']])) {
                            continue;
                        }

                        $updatedProductInfo[] = $product;
                    }

                    $statistic->setProductSkus(json_encode($updatedProductInfo))
                        ->setGiftCount($this->updateGiftCountValue($updatedProductInfo))->save();
                }
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    public function getAppliedRulesIds($quote)
    {
        $resultIds = [];
        $statistics = $this->getStatisticsByQuoteId($quote->getId());

        if ($statistics) {
            foreach ($statistics as $statistic) {
                $productInfo = json_decode($this->getProductInfo($statistic), true);

                if ($productInfo) {
                    $resultIds[] = $statistic->getRuleId();
                }
            }
        }

        return $resultIds;
    }

    /**
     * @param array $productInfo
     *
     * @return int
     */
    private function updateGiftCountValue($productInfo)
    {
        $resultQty = 0;

        foreach ($productInfo as $item) {
            if (isset($item['qty']) && $item['qty']) {
                $resultQty += $item['qty'];
            }
        }

        return $resultQty;
    }

    /**
     * @param \Aitoc\Gifts\Model\Statistic $model
     *
     * @return string
     */
    public function getProductInfo($model)
    {
        return $model->getData(self::PRODUCT_SKUS_FIELD_NAME);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param boolean $neededReset
     *
     * @return array
     */
    public function getGiftItemInQuoteFromStatistic($quote, $neededReset = true)
    {
        $items = [];
        $statistics = $this->getStatisticsByQuoteId($quote->getId());

        if ($statistics) {
            foreach ($statistics as $statistic) {
                $productInfo = json_decode($statistic->getProductSkus(), true);
                if ($productInfo) {
                    foreach ($productInfo as $product) {
                        $items[] = [
                            'sku' => $product['sku'],
                            'qty' => $product['qty'],
                        ];
                    }

                    if ($neededReset) {
                        $this->resetProductInfo($statistic);
                    }
                }
            }
        }

        return $items;
    }

    /**
     * @param \Aitoc\Gifts\Model\Statistic $statisticInstance
     *
     * @return \Aitoc\Gifts\Model\Statistic
     */
    public function resetProductInfo($statisticInstance)
    {
        $statisticInstance->setProductSkus(json_encode([]));
        $this->statisticResource->save($statisticInstance);

        return $statisticInstance;
    }
}
