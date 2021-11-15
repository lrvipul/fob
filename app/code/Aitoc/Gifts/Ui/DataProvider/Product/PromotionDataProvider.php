<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Ui\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Aitoc\Gifts\Model\RuleFactory;

class PromotionDataProvider extends ProductDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var \Aitoc\Gifts\Model\Rule
     */
    private $rule;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * PromotionDataProvider constructor.
     *
     * @param string                          $name
     * @param string                          $primaryFieldName
     * @param string                          $requestFieldName
     * @param CollectionFactory               $collectionFactory
     * @param RequestInterface                $request
     * @param RuleFactory $ruleFactory
     * @param StoreRepositoryInterface        $storeRepository
     * @param ProductLinkRepositoryInterface  $productLinkRepository
     * @param                                 $addFieldStrategies
     * @param                                 $addFilterStrategies
     * @param array                           $meta
     * @param array                           $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        RuleFactory $ruleFactory,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->productLinkRepository = $productLinkRepository;
        $this->ruleFactory = $ruleFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        $availableProductTypes = ['simple', 'virtual', 'downloadable'];
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToFilter('type_id', ['in' => $availableProductTypes]);

        if ($this->getStore()) {
            $collection->setStore($this->getStore());
        }

        if (!$this->getRule()) {
            return $collection;
        }

        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getRule()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }

    /**
     * Add specific filters
     *
     * @param Collection $collection
     * @return Collection
     */
    protected function addCollectionFilters(Collection $collection)
    {
        $promotionProducts = [];

        /** @var ProductLinkInterface $linkItem */
        foreach ($this->getRule()->getPoductSkus() as $sku) {
            $promotionProducts[] = $this->productRepository->get($sku)->getId();
        }

        if ($promotionProducts) {
            $collection->addAttributeToFilter(
                $collection->getIdFieldName(),
                ['nin' => [$promotionProducts]]
            );
        }

        return $collection;
    }

    /**
     * Retrieve rule
     *
     * @return \Aitoc\Gifts\Model\Rule|null
     */
    protected function getRule()
    {
        if (null !== $this->rule) {
            return $this->rule;
        }

        if (!($id = $this->request->getParam('current_rule_id'))) {
            return null;
        }

        return $this->rule = $this->ruleFactory->create()->getRuleById($id);
    }

    /**
     * Retrieve store
     *
     * @return StoreInterface|null
     */
    protected function getStore()
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if (!($storeId = $this->request->getParam('current_store_id'))) {
            return null;
        }

        return $this->store = $this->storeRepository->getById($storeId);
    }
}
