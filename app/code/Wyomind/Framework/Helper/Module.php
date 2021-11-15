<?php


/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;


use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Output\OutputInterface;
use Wyomind\Framework\Model\ResourceModel\ConfigFactory;

/**
 * Class Module
 * @package Wyomind\Framework\Helper
 */
class Module extends \Wyomind\Framework\Helper\License
{
    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $moduleList;
    /**
     * @var StoreManager
     */
    protected $storeManager;
    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * Module constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\App\Helper\Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\App\Helper\Context $context,
        StoreManager $storeManager,
        ConfigFactory $configFactory)
    {
        parent::__construct($objectManager, $context);
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->configFactory = $configFactory;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function moduleIsEnabled($moduleName)
    {
        return $this->moduleList->has($moduleName);
    }

    /**
     * @return array
     */
    public function getModuleList()
    {
        $list = $this->moduleList->getAll();
        $list = array_filter($list, function ($key) {
            return strpos($key, "Wyomind_") === 0 && $key !== "Wyomind_Framework" && $key !== "Wyomind_Core";
        }, ARRAY_FILTER_USE_KEY);
        return $list;
    }

    public function updateConfigPubFolderEnabled(OutputInterface $output = null)
    {

        $config = $this->configFactory->create();


        $url = str_replace("{{unsecure_base_url}}", $config->getDefaultValueByPath("web/unsecure/base_url"), $config->getDefaultValueByPath("web/secure/base_url"));
        if ($url == "") {
            $url = str_replace("{{unsecure_base_url}}", $this->getStoreConfig("web/unsecure/base_url"), $this->getStoreConfig("web/secure/base_url"));
        }
        //$url .= "wframework/utils/pub";

        $originalUrl = $url;
        $urls = [$url . " "];
        $urls[] = preg_replace('|:[0-9]+/|', '/', $url);
        $httpUrl = preg_replace('|https://|', 'http://', $url);;
        $urls[] = $httpUrl;
        $urls[] = preg_replace('|:[0-9]+/|', '/', $httpUrl);


        $isPubUsed = null;

        foreach ($urls as $url) {
            if ($url == $originalUrl) {
                continue;
            }
            $output->writeln('<comment>Checking pub/ directory for  ' . $url . ' ...</comment>');
            list($curlOutput, $errors) = $this->executeCurl($url);

            if ($errors === "") {
                $isPubUsed =
                    strpos($curlOutput, "/pub/media/") !== false
                    || strpos($curlOutput, "[/pub/index.php") !== false
                    || strpos($curlOutput, "/pub/errors/") !== false
                ; // $curlOutput === "1";
            } else {
                $output->writeln(__('<error>Not able to determine if the folder "pub" is in use. Trying next url.</error>'));
                continue;
            }

            if ($isPubUsed === true) {
                $output->writeln(__('<info>The folder "pub" is in use.</info>'));
                break;
            }
            if ($isPubUsed === false) {
                $output->writeln(__('<info>The folder "pub" is not in use.</info>'));
                break;
            }
        }

        if ($isPubUsed === null) {
            $output->writeln(__('<error>Not able to determine if the folder "pub" is in use. Fallback to "not in use".</error>'));
            $output->writeln(__("<error>\nPlease run the command\nbin/magento wyomind:tools:pub\nafter having run setup:upgrade</error>\n"));
            $isPubUsed = false;
        }

        $this->setDefaultConfig('wyomind_framework/use_pub_folder', $isPubUsed);
    }

    public function isPubFolderInUse()
    {
        return $this->getDefaultConfig('wyomind_framework/use_pub_folder');
    }


    public function executeCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $curlOutput = curl_exec($ch);
        $errors = curl_error($ch);
        curl_close($ch);
        return [$curlOutput, $errors];
    }
}

