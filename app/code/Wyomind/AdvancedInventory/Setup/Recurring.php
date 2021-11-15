<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\AdvancedInventory\Setup;

class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{
    private $_framework = null;

    /**
     * Recurring constructor.
     * @param \Wyomind\Framework\Helper\Install $framework
     */
    public function __construct(
        \Wyomind\Framework\Helper\Install $framework
    )
    {
        $this->_framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        $files = [
            "Observer/RefundOrderInventoryObserver.php"
        ];
        $this->_framework->copyFilesByMagentoVersion(__FILE__, $files);
    }
}