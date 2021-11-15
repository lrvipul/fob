<?php

namespace Wyomind\AdvancedInventory\Controller\Update;

use Magento\Framework\App\Action\Context;

class Stocks extends \Magento\Framework\App\Action\Action
{

    private $_storeManager;
    private $_productRepository;
    private $_helperData;
    private $_modelEavConfig;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Eav\Model\Config $modelEavConfig
    )
    {
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_modelEavConfig = $modelEavConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $product = $this->_productRepository->getById($this->getRequest()->getParam("productId"));
        $block = $this->_view->getLayout()->createBlock('Wyomind\AdvancedInventory\Block\Catalog\Product\Stock');
        $html = $block->output($product, "grid", false, true);
        $json = $block->output($product, "json");
        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode(["html" => $html, "stocks" => $json]));

    }

}
