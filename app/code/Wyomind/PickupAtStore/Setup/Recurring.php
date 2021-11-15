<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\PickupAtStore\Setup;

class Recurring implements \Magento\Framework\Setup\InstallSchemaInterface
{


    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    private $_framework = null;

    /**
     * Recurring constructor.
     * @param \Wyomind\Framework\Helper\Install $framework
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
    \Wyomind\Framework\Helper\Install $framework,
        \Magento\Framework\App\State $state
    )
    {
        $this->_framework = $framework;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context)
    {

        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) {

        }

        $files = [
            "Model/Carrier/PickupAtStore.php",
            "view/frontend/web/template/billing-address.html",
            "view/frontend/web/template/shipping.html",
            "view/frontend/web/js/view/shipping.js",
            "view/frontend/email/order_new_guest_pickupatstore.html",
            "view/frontend/email/order_new_pickupatstore.html"
        ];
        $this->_framework->copyFilesByMagentoVersion(__FILE__, $files);
    }

}
