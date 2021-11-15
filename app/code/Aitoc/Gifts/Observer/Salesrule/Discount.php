<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Observer\Salesrule;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class Discount implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\State $state
    ) {
        $this->productRepository = $productRepository;
        $this->state = $state;
    }

    /**
     * @param Observer $observer
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data|void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getItem();
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $result */
        $result = $observer->getResult();
    }
}
