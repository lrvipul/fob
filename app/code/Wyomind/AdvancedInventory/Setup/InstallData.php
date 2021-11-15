<?php

namespace Wyomind\AdvancedInventory\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    protected $_framework = null;

    public function __construct(
        \Wyomind\Framework\Helper\Module $framework
    ) {
        $this->_framework = $framework;
    }

    /**
     * @version 6.0.0
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        unset($context);

        $installer = $setup;
        $installer->startSetup();
        $installer->endSetup();
    }
}
