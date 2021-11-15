<?php
namespace IWD\Opc\Controller\Index;

class Testing extends \Magento\Framework\App\Action\Action
{

    protected $_orderFactory;
    protected $_filesystem;
    protected $_dirList;
    protected $_csvWriter;
    protected $_fileFactory;
    protected $_productRepo;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\Order $orderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csvWriter,
        \Magento\Catalog\Model\ProductRepository $productRepo,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $dirList
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_filesystem = $filesystem;
        $this->_csvWriter = $csvWriter;
        $this->_productRepo = $productRepo;
        $this->_fileFactory = $fileFactory;
        $this->_dirList = $dirList;
        parent::__construct($context);
    }

    public function execute()
    {
        die("");
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customOrder.log');
        $logger = new \Zend\Log\Logger();

        $logger->addWriter($writer);
        $logger->info("OrderSubmitAllAfter");
        $timeValue = date("d-m-Y h:i:s");
        $logger->info("time is = ".$timeValue);

        //$orderData = $observer->getData('order');
        //$orderId = $orderData->getId();

        $orderId = 3284;


        $logger->info("order ID is = ".$orderId);
        $timeValue = date("d-m-Y h:i:s");
        $logger->info("time is = ".$timeValue);


        $order = $this->_orderFactory->load($orderId); 

        if (strstr($order->getShippingMethod(), "pickupatstore")) 
        {
            if($order->getPickupStore() != '')
            {
                
                $rows = []; 
                $qwe = $order->getPickupStore();

                /**** csv header *****/
                $heading = array("Order Number","Forename","Surname","Address1","Address2","Address3","Address4","Eircode","Total Transaction","Cost of Delivery","SKU","Selling Price","Quantity Sold","ICR Record","Date/Time","Store","Order Type","Finalised","Methods of Finalisation","Discounts");
                $rows[] = $heading;

                $mediaPath =  $this->_dirList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                
                $orderCsvPath = $mediaPath."/orderCsvs";

                $exist =  is_dir($orderCsvPath); 

                if(!is_dir($orderCsvPath))
                {
                   mkdir($orderCsvPath, 0777, true);
                }

                $orderIncrementId = $order->getIncrementId();

                $outputFile = $orderCsvPath."/".$orderIncrementId.'.csv';

                $fileName = $orderIncrementId.'.csv';

                $customerFirstName = $order['customer_firstname'];
                $customerLastName = $order['customer_lastname'];
                $transactionTotal = $order['grand_total'];
                $deliveryCharges = $order['shipping_amount'];

                $shippingAddress = $order->getShippingAddress();
                $streetDetails = $shippingAddress->getStreet();
                
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

                $i=0;
                foreach($items as $item)
                {
                   // $store = (isset($qwe[$i])) ? $qwe[$i] : $qwe[0];
                   $store = $qwe;
                   $i++;
                   $sku = $item->getSku();
                   $productPrice =$item->getPrice();    
                   $qty = $item->getData('qty_ordered');    
                   
                   $product = $this->_productRepo->getById($item->getProductId());
                   $icrCodeValue = $product->getIcrRecord();

                   $attributes = $product->getAttributes();

                   $rows[] = array($orderIncrementId,$customerFirstName,$customerLastName,$addressStreet1,$addressStreet2,$addressStreet3,$addressStreet4,$eircode,$transactionTotal,$deliveryCharges,$sku,$productPrice,$qty,$icrCodeValue,$createdDate,$store,$order_type,$finalised,$order->getPayment()->getMethodInstance()->getTitle(),$order->getDiscountAmount());      
                            
                }     

                $this->_csvWriter
                    ->setEnclosure('"')
                    ->setDelimiter(',')
                    ->saveData($outputFile ,$rows);

               /* echo $outputFile; die;

                $this->_fileFactory->create(
                   $fileName,
                   [
                       'type'  => "csv",
                       'value' => $fileName,
                       'rm'    => true,
                   ],
                   \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
                   'text/csv',
                   null
                );*/

            }

            die("outerererereer");
        }
    }
}