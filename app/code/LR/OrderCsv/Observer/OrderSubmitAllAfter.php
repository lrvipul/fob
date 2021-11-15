<?php
namespace LR\OrderCsv\Observer;

class OrderSubmitAllAfter implements \Magento\Framework\Event\ObserverInterface
{
   protected $_orderFactory;
   protected $_dirList;
   protected $_csvWriter;
   protected $_productRepo;

   public function __construct(
      \Magento\Sales\Model\Order $orderFactory,
      \Magento\Framework\File\Csv $csvWriter,
      \Magento\Catalog\Model\ProductRepository $productRepo,
      \Magento\Framework\Filesystem\DirectoryList $dirList
   )
   {
        $this->_orderFactory = $orderFactory;
        $this->_csvWriter = $csvWriter;
        $this->_productRepo = $productRepo;
        $this->_dirList = $dirList;
   }

   public function execute(\Magento\Framework\Event\Observer $observer)
   {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customOrder.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);      
      
      $logger->info("call execute method");
      $orderData = $observer->getData('order');
      $orderId = $orderData->getId();
      $logger->info($orderId);
      $order = $this->_orderFactory->load($orderId); 
      $logger->info($order->getShippingMethod());

      if (strstr($order->getShippingMethod(), "pickupatstore")) 
      {
         $logger->info("Enter in pickupatstore");
         if($order->getPickupStore() != '')
         {
            $rows = []; 
            $qwe = $order->getPickupStore();

            /**** csv header *****/
            $heading = array("Order Number","Forename","Surname","Address1","Address2","Address3","Address4","Eircode","Total Transaction","Cost of Delivery","SKU","Selling Price","Quantity Sold","ICR Record","Date/Time","Store","Order Type","Finalised","Methods of Finalisation","Discounts");
            $rows[] = $heading;

            //$mediaPath =  $this->_dirList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

            //$orderCsvPath = $mediaPath."/orderCsvs";
            $rootPath =  $this->_dirList->getRoot();
            $orderCsvPath = $rootPath."/order";
            $logger->info($orderCsvPath);

            if(!is_dir($orderCsvPath))
            {
               mkdir($orderCsvPath, 0777, true);
            }

            $orderIncrementId = $orderId; //$order->getIncrementId();
            $orderIncrementId = $order->getIncrementId();

            $logger->info("Order ID => ".$orderIncrementId);

            $outputFile = $orderCsvPath."/".$orderIncrementId.'.csv';

            $fileName = $orderIncrementId.'.csv';
            $logger->info("Order CSV File Path => ".$outputFile);

            $customerFirstName = $order['customer_firstname'];
            $customerLastName = $order['customer_lastname'];
            $transactionTotal = $order['grand_total'];
            $deliveryCharges = $order['shipping_amount'];
            
            $shippingAddress = $order->getShippingAddress();
            $streetDetails = $shippingAddress->getStreet();
            $addressStreet1 = $streetDetails[0]; 
            if(isset($streetDetails[1]))
               $addressStreet1 = $streetDetails[0]." ".$streetDetails[1];
            $addressStreet1 = ($addressStreet1) ? str_replace(',', '-', $addressStreet1) : "";

            $addressStreet2 = $shippingAddress->getCity();
            $addressStreet2Valid = str_replace(array(" ","&nbsp;"), array("", ""), $addressStreet2);
            $addressStreet2 = ($addressStreet2 && $addressStreet2Valid) ? str_replace(',', '-', $addressStreet2) : "";

            $addressStreet3 = $shippingAddress->getPostcode();
            $addressStreet3Valid = str_replace(array(" ","&nbsp;"), array("", ""), $addressStreet3);
            $addressStreet3 = ($addressStreet3 && $addressStreet3Valid) ? str_replace(',', '-', $addressStreet3) : "";

            $addressStreet4 = $shippingAddress->getRegion();
            $addressStreet4Valid = str_replace(array(" ","&nbsp;"), array("", ""), $addressStreet4);
            $addressStreet4 = ($addressStreet4 && $addressStreet4Valid) ? str_replace(',', '-', $addressStreet4) : "";
            $createdDate = $order->getCreatedAt();

            
            $items = $order->getAllVisibleItems();
            $eircode = '';
            $order_type = 'Click and Collect';

            if($order->getShippingMethod() == 'tablerate_bestway')
            {
               $order_type='Web Order';
            }

            $finalised = "Y";
            foreach($items as $item)
            {
               $store = $qwe;
               $sku = $item->getSku();
               $productPrice =$item->getPrice();    
               $qty = $item->getData('qty_ordered');    

               $product = $this->_productRepo->getById($item->getProductId());
               $icrCodeValue = $product->getIcrRecord();

               $rows[] = array($orderIncrementId,$customerFirstName,$customerLastName,$addressStreet1,$addressStreet2,$addressStreet3,$addressStreet4,$eircode,$transactionTotal,$deliveryCharges,$sku,$productPrice,$qty,$icrCodeValue,$createdDate,$store,$order_type,$finalised,$order->getPayment()->getMethodInstance()->getTitle(),$order->getDiscountAmount());
            }     

            $this->_csvWriter
              ->setEnclosure('"')
              ->setDelimiter(',')
              ->saveData($outputFile ,$rows);
         }
      }
      return $this;
   }
}