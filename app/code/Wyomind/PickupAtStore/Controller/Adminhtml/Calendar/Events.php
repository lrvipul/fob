<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Controller\Adminhtml\Calendar;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderRepository;
use Wyomind\PickupAtStore\Helper\Data;
use Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory;

/**
 * Class Index
 * @package Wyomind\PickupAtStore\Controller\Adminhtml\Calendar
 */
class Events extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    protected $filterBuiler;
    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;
    /**
     * @var CollectionFactory
     */
    protected $posCollectionFactory;
    /**
     * @var Json
     */
    protected $jsonHelper;

    /**
     * @var array
     */
    private $sourcesInfos = [];

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuiler
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param CollectionFactory $posCollectionFactory
     * @param Json $jsonHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuiler,
        FilterGroupBuilder $filterGroupBuilder,
        CollectionFactory $posCollectionFactory,
        Json $jsonHelper)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuiler = $filterBuiler;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->posCollectionFactory = $posCollectionFactory;
        $this->jsonHelper = $jsonHelper;
    }

    public function getSourcesInfos()
    {


        $this->sourcesInfos = [];
        $collection = $this->posCollectionFactory->create();
        $i = 0;
        foreach ($collection as $pos) {
            if (empty($pos->getCalendarColor())) {
                $color = Data::$calendarColors[($i++%count(Data::$calendarColors))];
            } else {
                $color = $pos->getCalendarColor();
            }
            $this->sourcesInfos[$pos->getId()] = [
                'color' => $color,
                'name' => $pos->getName()
            ];
        }
    }

    public function execute()
    {
        $data = [];

        $start = $this->getRequest()->getParam('start');
        $end = $this->getRequest()->getParam('end');

        $this->filterBuiler->setConditionType("gteq");
        $this->filterBuiler->setValue(date('Y-m-d', strtotime($start)));
        $this->filterBuiler->setField("pickup_datetime");
        $filter = $this->filterBuiler->create();
        $this->searchCriteriaBuilder->addFilter($filter);

        $this->filterBuiler->setConditionType("lt");
        $this->filterBuiler->setValue(date('Y-m-d', strtotime($end)));
        $this->filterBuiler->setField("pickup_datetime");
        $filter = $this->filterBuiler->create();
        $this->searchCriteriaBuilder->addFilter($filter);

        $this->filterBuiler->setConditionType("nin");
        $this->filterBuiler->setValue(["fraud", "holded", "canceled"]);
        $this->filterBuiler->setField("status");
        $filter = $this->filterBuiler->create();
        $this->searchCriteriaBuilder->addFilter($filter);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orders = $this->orderRepository->getList($searchCriteria);


        if ($orders->getTotalCount() > 0) {
            $this->getSourcesInfos();
        }

        foreach ($orders->getItems() as $order) {
            if (is_numeric($order->getPickupStore())) {
                $customer = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();
                if ($customer == " ") {
                    $customer = $order->getBillingAddress()->getFirstname() . " " . $order->getBillingAddress()->getLastname();
                }
                if (substr($order->getPickupDatetime(), -6) == " 00:00") {
                    $data[] = [
                        "title" => $customer . "\nINCREMENT_ID",
                        "start" => $order->getPickupDatetime(),
                        "color" => $this->sourcesInfos[$order->getPickupStore()]['color'],
                        "order_url" => $this->getUrl("sales/order/view", ["order_id" => $order->getId()]),
                        "allDay" => true,
                        "classNames" => [$order->getPickupStore()],
                        "description" => $this->getOrderDescription($order, true),
                        "increment_id" => $order->getIncrementId()
                    ];
                } else {
                    $data[] = [
                        "title" => $customer . "\nINCREMENT_ID",
                        "start" => $order->getPickupDatetime(),
                        "end" => $order->getPickupDatetime(),
                        "color" => $this->sourcesInfos[$order->getPickupStore()]['color'],
                        "order_url" => $this->getUrl("sales/order/view", ["order_id" => $order->getId()]),
                        "classNames" => [$order->getPickupStore()],
                        "description" => $this->getOrderDescription($order),
                        "increment_id" => $order->getIncrementId()
                    ];
                }
            }
        }

        return $this->getResponse()->representJson(
            $this->jsonHelper->serialize($data)
        );

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getOrderDescription($order, $allDay = false)
    {
        $customer = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();
        if ($customer == " ") {
            $customer = $order->getBillingAddress()->getFirstname() . " " . $order->getBillingAddress()->getLastname();
        }
        $description = "<span class='order-increment-id'><label>Order</label>" . $order->getIncrementId() . "</span>";
        $description .= "<span class='order-store'><label>Store</label>" . $this->sourcesInfos[$order->getPickupStore()]['name'] . "</span>";
        $description .= "<span class='order-customer'><label>Customer</label>" . $customer . "</span>";
        if ($allDay) {
            $description .= "<span class='order-collect-datetime'><label>Collect date</label>" . date('Y-m-d', strtotime($order->getPickupDatetime())) . "</span>";
        } else {
            $description .= "<span class='order-collect-datetime'><label>Collect time</label>" . date('Y-m-d H:i', strtotime($order->getPickupDatetime())) . "</span>";
        }
        return $description;
    }
}
