<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface as MetaPoolInterface;
use Aitoc\Gifts\Model\Pool\Action;
use Aitoc\Gifts\Model\ResourceModel\Rule\CollectionFactory;
use Aitoc\Gifts\Model\RuleFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Action
     */
    private $actionPool;

    /**
     * @var MetaPoolInterface
     */
    private $pool;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RuleFactory $ruleFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        Action $actionPool,
        MetaPoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->actionPool = $actionPool;
        $this->pool = $pool;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $actionList = $this->actionPool->getActions();
        $action = $this->request->getParam('action', array_keys($actionList)[0]);

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var Rule $rule */
        foreach ($items as $rule) {
            $model = $this->ruleFactory->create()->load($rule->getId());
            $this->loadedData[$rule->getId()] = $this->prepareToLoadData($model->getData());
        }

        $data = $this->dataPersistor->get('aitoc_gifts_rule');

        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('aitoc_gifts_rule');
        }

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        if ($this->loadedData) {
            $ruleKeys = array_keys($this->loadedData);
            $ruleId = array_shift($ruleKeys);
            $this->data[$ruleId] =
                isset($this->data[$ruleId])
                    ? $this->data[$ruleId] + $this->loadedData[$ruleId] : $this->loadedData[$ruleId];
        }

        return $this->data;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private function prepareToLoadData($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
