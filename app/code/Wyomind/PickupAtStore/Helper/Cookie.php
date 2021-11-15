<?php

/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 05/10/2020
 * Time: 15:05
 */
namespace Wyomind\PickupAtStore\Helper;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
class Cookie extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    public function __construct(\Wyomind\PickupAtStore\Helper\Delegate $wyomind, CookieMetadataFactory $cookieMetadataFactory)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }
    /**
     * @param $key
     * @param $value
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setCookie($key, $value)
    {
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
        $this->cookieManager->setPublicCookie($key, $value, $publicCookieMetadata);
    }
    public function getCookie($key)
    {
        $this->cookieManager->getCookie($key);
    }
}