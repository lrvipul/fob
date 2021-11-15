<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Install
 * @package Wyomind\Framework\Helper
 */
class Install extends \Wyomind\Framework\Helper\License
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $consoleOutput;

    /**
     * Install constructor.
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Symfony\Component\Console\Output\ConsoleOutput $consoleOutput
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Helper\Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Symfony\Component\Console\Output\ConsoleOutput $consoleOutput,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        parent::__construct($objectManager, $context);

        $this->driverFile = $driverFile;
        $this->consoleOutput = $consoleOutput;
    }

    /**
     * @param $files
     */
    public function replaceInFiles($currentScript, $files)
    {
        try {
            $path = str_replace(["Setup" . DIRECTORY_SEPARATOR . "UpgradeData.php", "Setup" . DIRECTORY_SEPARATOR . "Recurring.php"], ["", ""], $currentScript);
            foreach ($files as $file => $replacement) {
                $fullFile = $path . str_replace("/", DIRECTORY_SEPARATOR, $file);
                $content = $this->driverFile->fileGetContents($fullFile);
                $content = str_replace($replacement['from'], $replacement['to'], $content);
                $this->driverFile->filePutContents($fullFile, $content);
            }
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param $currentScript
     * @param $files
     */
    public function copyFilesByMagentoVersion($currentScript, $files)
    {
        try {
            $this->consoleOutput->writeln("");

            // Magento version
            $version = $this->getMagentoVersion();

            $this->consoleOutput->writeln("<comment>Copying files for Magento " . $version . "</comment>");


            $explodedVersion = explode(".", $version);
            $possibleVersion = [
                $version,
                $explodedVersion[0] . "." . $explodedVersion[1],
                $explodedVersion[0]
            ];

            $path = str_replace(["Setup" . DIRECTORY_SEPARATOR . "UpgradeData.php", "Setup" . DIRECTORY_SEPARATOR . "Recurring.php"], ["", ""], $currentScript);


            foreach ($files as $file) {
                $fullFile = $path . str_replace("/", DIRECTORY_SEPARATOR, $file);
                $ext = pathinfo($fullFile, PATHINFO_EXTENSION);
                foreach ($possibleVersion as $v) {
                    $newFile = str_replace("." . $ext, "_" . $v . "." . $ext, $fullFile);
                    if (file_exists($newFile)) {
                        copy($newFile, $fullFile);
                        $classnameVersion = str_replace("." . $ext, "_" . str_replace(".", "_", $v), basename($file));
                        $classname = str_replace("." . $ext, "", basename($file));
                        $content = file_get_contents($fullFile);
                        if (preg_match_all("/" . $classnameVersion . "/", $content)) {
                            $content = str_replace($classnameVersion, $classname, $content);
                            file_put_contents($fullFile, $content);
                        }
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            return;
        }
    }
}