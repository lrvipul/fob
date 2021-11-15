<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\PickupAtStore\Controller\Update;

/**
 * Controller to update the shipping date and hour
 */
class Shippingmethod extends \Magento\Framework\App\Action\Action
{
    protected $_jsonHelper = null;
    protected $_quoteRepository = null;
    protected $_checkoutSession = null;
    protected $_backendQuote = null;
    protected $_framework = null;
    protected $_posCollectionFactory = null;
    protected $_posHelperFactory = null;
    protected $_configHelper = null;
    protected $_pasHelper = null;
    protected $_region = null;
    protected $_logger = null;

    /**
     * Class constructor
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuote
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory $posCollectionFactory
     * @param \Wyomind\PointOfSale\Helper\DataFactory $posHelperFactory
     * @param \Wyomind\PickupAtStore\Helper\Config $configHelper
     * @param \Wyomind\PickupAtStore\Helper\Data $cacHelper
     * @param \Magento\Directory\Model\Region $region
     * @param \Wyomind\PickupAtStore\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuote,
        \Wyomind\Framework\Helper\Module $framework,
        \Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory $posCollectionFactory,
        \Wyomind\PointOfSale\Helper\DataFactory $posHelperFactory,
        \Wyomind\PickupAtStore\Helper\Config $configHelper,
        \Wyomind\PickupAtStore\Helper\Data $cacHelper,
        \Magento\Directory\Model\Region $region,
        \Wyomind\PickupAtStore\Logger\Logger $logger
    )
    {
        $this->_jsonHelper = $jsonHelper;
        $this->_quoteRepository = $quoteRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->_backendQuote = $backendQuote;
        $this->_framework = $framework;
        $this->_posCollectionFactory = $posCollectionFactory;
        $this->_posHelperFactory = $posHelperFactory;
        $this->_configHelper = $configHelper;
        $this->_pasHelper = $cacHelper;
        $this->_region = $region;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Update the pickup date time in the quote if needed
     * @throws InputException
     */
    public function execute()
    {
        try {
            if ($this->_framework->isAdmin()) {
                $quote = $this->_backendQuote->getQuote();
            } else {
                $quote = $this->_checkoutSession->getQuote();
            }

            $params = $this->getRequest()->getParams('data');
            if (isset($params['data'])) {
                $data = $params['data'];
            } else {
                $data = [];
            }
            
            if (isset($data['store'])) {
                if (isset($data['date'])) {
                    $datetime = $data['date'];
                    if (isset($data['time']) && $data['time'] != '0') {
                        $datetime .= " " . $data['time'];
                    } else {
                        $datetime .= " 00:00";
                    }
                    $quote->setPickupDatetime($datetime);
                }
                $quote->setPickupStore($data['store']);
//                try {
//                    $this->_quoteRepository->save($quote);
//                } catch (\Exception $e) {
//                    throw new \Magento\Framework\Exception\InputException(__('Unable to save shipping information. Please check input data.'));
//                }
            } else {
                $quote->setPickupDatetime(null);
                $quote->setPickupStore(null);
                $quote->save();
            }

            if ($quote->getPickupStore()) {
                $storeId = $quote->getPickupStore();
                $store = $this->_posCollectionFactory->create()->getPlace($storeId)->getFirstItem();

                $storeDetails = $store->getName() . ' ';
                $storeDetails .= " [ ";
                $o = 0;
                if ($store->getAddressLine1()) {
                    $storeDetails .= $store->getAddressLine1();
                    $o++;
                }
                if ($store->getAddressLine2()) {
                    if ($o) {
                        $storeDetails .= ", ";
                    }
                    $storeDetails .= $store->getAddressLine2();
                    $o++;
                }
                if ($store->getCity()) {
                    if ($o) {
                        $storeDetails .= ", ";
                    }
                    $storeDetails .= $store->getCity();
                    $o++;
                }
                if ($store->getState()) {
                    if ($o) {
                        $storeDetails .= ", ";
                    }
                    $storeDetails .= $store->getState();
                    $o++;
                }
                if ($store->getPostalCode()) {
                    if ($o) {
                        $storeDetails .= ", ";
                    }
                    $storeDetails .= $store->getPostalCode() . " ";
                }
                $storeDetails .= " ]";
                $storeDetails .= "\n";

                $storeDetails .= str_replace("<br>", "\n", $this->_posHelperFactory->create()->getHours($store->getHours()));

                if ($quote->getPickupDatetime()) {
                    if ($this->_configHelper->getTime()) {
                        $storeDetails .= "\n" . __('Your pickup time: ') . $this->_pasHelper->formatDatetime($quote->getPickupDatetime()) . "\n\n";
                    } elseif ($this->_configHelper->getDate()) {
                        $storeDetails .= "\n" . __('Your pickup date: ') . $this->_pasHelper->formatDate($quote->getPickupDatetime()) . "\n\n";
                    }
                }

                $quote->setShippingDescription($storeDetails)->save();

                $region = $this->_region->loadByCode($store->getState(), $store->getCountryCode());
                $shippingData = [
                    "shipping_method" => "pickupatstore_pickupatstore_" . $store->getId(),
                    "prefix" => "",
                    "firstname" => __("Store Pickup"),
                    "middlename" => "",
                    "lastname" => $store->getName(),
                    "suffix" => "",
                    "company" => "",
                    "street" => $store->getAddressLine1() . ($store->getAddressLine2() ? "\n" . $store->getAddressLine2() : ''),
                    "city" => $store->getCity(),
                    "region" => $region->getDefaultName(),
                    "region_id" => $region->getRegionId()? : "0",
                    "postcode" => $store->getPostalCode(),
                    "country_id" => $store->getCountryCode(),
                    "telephone" => $store->getMainPhone()? : "0000000000",
                    "fax" => "",
                    "email" => $store->getEmail()? : "no@contact.com",
                    "save_in_address_book" => false
                ];

                $quote->setShippingMethod("pickupatstore_pickupatstore_" . $store->getPlaceId())->save();
                $quote->getShippingAddress()->addData($shippingData)->save();
                $shippingAddress =  $quote->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true);
                $shippingAddress->save();
                $quote->getShippingAddress()->collectShippingRates();

            }
            $this->getResponse()->representJson($this->_jsonHelper->jsonEncode(["error" => false, "message" => "Pickup datetime saved"]));
        } catch (\Exception $exception) {
            $this->getResponse()->representJson($this->_jsonHelper->jsonEncode(["error" => true, "message" => $exception->getMessage()]));
        }
    }
}
