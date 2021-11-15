<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Observer\Quote;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AddProduct implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Aitoc\Gifts\Model\Repository\StatisticRepository
     */
    private $statisticRepository;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\State $state,
        \Aitoc\Gifts\Model\Repository\StatisticRepository $statisticRepository
    ) {
        $this->productRepository = $productRepository;
        $this->state = $state;
        $this->statisticRepository = $statisticRepository;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getItem();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();
        /** @var \Aitoc\Gifts\Model\Rule $result */
        $rule = $observer->getRule();

        if ($rule->getId()) {
            if ($quote->getId()) {
                $statistic = $this->statisticRepository->getStatisticByQuoteAndRule($quote->getId(), $rule->getId());
            }
            if (empty($statistic)) {
                $statistic = $this->statisticRepository->createStatisticModel();
            }

            $this->statisticRepository->addNewStatisticData($statistic, $quote, $item, $rule);
        }

        return $this;
    }
}
