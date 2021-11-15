<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Magento\Sales\Model\Order\Email\Sender;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Wyomind\PickupAtStore\Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObject;
use Wyomind\Framework\Helper\Module;


class OrderSender extends Order\Email\Sender\OrderSender
{
    /**
     * @var Data
     */
    protected $framework;


    /**
     * OrderSender constructor.
     * @param Template $templateContainer
     * @param OrderIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param OrderResource $orderResource
     * @param ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     * @param Framework $framework
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Module $framework
    )
    {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $orderResource, $globalConfig, $eventManager);
        $this->framework = $framework;
    }

    /**
     * Populate order email template with customer information.
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {

        if (!$order->isPickupAtStore()) {
            parent::prepareTemplate($order);
            return;
        }

        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'created_at_formatted' => $order->getCreatedAtFormatted(2),
            'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'is_not_virtual' => $order->getIsNotVirtual(),
                'email_customer_note' => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel()
            ],
            'pickup_at_store' => [
                'store_name' => $order->getPickupAtStoreStoreName(),
                'hours' => $order->getPickupAtStoreStoreHours(),
                'code' => $order->getPickupAtStoreStoreCode(),
                'datetime' => $order->getPickupAtStoreDatetime(),
                'date' => $order->getPickupAtStoreDate()
            ]
        ];

        $transportObject = new DataObject($transport);


        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
        $this->eventManager->dispatch(
            'email_order_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject, 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transportObject->getData());
        $this->templateContainer->setTemplateOptions($this->getTemplateOptions());

        if ($order->getCustomerIsGuest()) {
            $templateId = $this->identityContainer->getGuestTemplateId();
            if ($order->isPickupAtStore()) {
                $this->identityContainer->setPickupStore($order->getPickupAtStoreStore());
                $templateId = $this->framework->getStoreConfig("carriers/pickupatstore/emails/new_order_guest", $order->getStoreId());
            }
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = $this->identityContainer->getTemplateId();
            $customerName = $order->getCustomerName();
            if ($order->isPickupAtStore()) {
                $this->identityContainer->setPickupStore($order->getPickupAtStoreStore());
                $templateId = $this->framework->getStoreConfig("carriers/pickupatstore/emails/new_order", $order->getStoreId());
            }
        }
        $this->identityContainer->setCustomerName($customerName);
        $this->identityContainer->setCustomerEmail($order->getCustomerEmail());
        $this->templateContainer->setTemplateId($templateId);

    }


}