<?php

/**
 * Copyright © 2019 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wyomind\PickupAtStore\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $fileName = '/var/log/PickupAtStore.log';
    protected $loggerType = \Monolog\Logger::NOTICE;
    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     */
    public function __construct(\Magento\Framework\Filesystem\DriverInterface $filesystem, $filePath = null)
    {
        parent::__construct($filesystem, $filePath);
        $this->setFormatter(new \Monolog\Formatter\LineFormatter("[%datetime%] %message%
", null, true));
    }
}