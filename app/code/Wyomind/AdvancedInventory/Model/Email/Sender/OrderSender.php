<?php

namespace Wyomind\AdvancedInventory\Model\Email\Sender;

use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Sales\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface;
class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    /**
     * OrderSender constructor.
     * @param \Magento\Sales\Model\Order\Email\Container\Template $templateContainer
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $identityContainer
     * @param \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(Template $templateContainer, OrderIdentity $identityContainer, SenderBuilderFactory $senderBuilderFactory, LoggerInterface $logger, Renderer $addressRenderer, Data $paymentHelper, Order $orderResource, ScopeConfigInterface $globalConfig, ManagerInterface $eventManager)
    {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $orderResource, $globalConfig, $eventManager);
    }
    public function prepareCopy($order)
    {
        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $order->getStore()->getId()];
        $this->templateContainer->setTemplateOptions($templateOptions);
        $transport = ['order' => $order, 'billing' => $order->getBillingAddress(), 'payment_html' => $this->getPaymentHtml($order), 'store' => $order->getStore(), 'formattedShippingAddress' => $this->getFormattedShippingAddress($order), 'formattedBillingAddress' => $this->getFormattedBillingAddress($order)];
        $transportObject = new DataObject($transport);
        $this->templateContainer->setTemplateVars($transportObject->getData());
        if ($order->getCustomerIsGuest()) {
            $templateId = $this->identityContainer->getGuestTemplateId();
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = $this->identityContainer->getTemplateId();
            $customerName = $order->getCustomerName();
        }
        $this->identityContainer->setCustomerName($customerName);
        $this->templateContainer->setTemplateId($templateId);
        return true;
    }
    public function sendCopy($email)
    {
        $this->identityContainer->setCustomerEmail($email);
        $sender = $this->senderBuilderFactory->create(['templateContainer' => $this->templateContainer, 'identityContainer' => $this->identityContainer]);
        $sender->send();
    }
}