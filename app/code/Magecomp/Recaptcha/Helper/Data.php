<?php
namespace Magecomp\Recaptcha\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CAPTCHA_ENABLE = 'recaptcha/general/enable';
    const SITE_KEY = 'recaptcha/general/sitekey';
    const SECRET_KEY = 'recaptcha/general/secretkey';
    const THEME_OPTION = 'recaptcha/general/theme';
    const CUST_REG = 'recaptcha/general/custreg';
    const CUST_LOGIN = 'recaptcha/general/loginpage';
    const REC_NEWSLETTER = 'recaptcha/general/newsletter';
    const PROD_REVIEW = 'recaptcha/general/prodreview';
    const FORGOTPASSWORD = 'recaptcha/general/forgotpassword';
    const CHECKOUTREGISTER = 'recaptcha/general/checkoutregister';
    const CONTACTUS = 'recaptcha/general/contactus';

    protected $_modelStoreManagerInterface;
    protected $_frameworkRegistry;
    protected $filesystem;
    protected $localeresolver;

    public function __construct(Context $context,
                                StoreManagerInterface $modelStoreManagerInterface,
                                Resolver $localeresolver
    )
    {
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->localeresolver = $localeresolver;
        parent::__construct($context);
    }
    public function getCurrentStoreInfo()
    {
        return $this->_modelStoreManagerInterface->getStore()->getId();
    }
    public function getLocal()
    {
        return strstr($this->localeresolver->getLocale(), '_', true);
    }
    public function getSiteKey()
    {
        return $this->scopeConfig->getValue(self::SITE_KEY, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
    }
    public function getSecretkey()
    {
        return $this->scopeConfig->getValue(self::SECRET_KEY, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
    }
    public function getThemeOption1()
    {
        return $this->scopeConfig->getValue(self::THEME_OPTION, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
    }
    public function IS_ENABLE()
    {
        return (bool)$this->scopeConfig->getValue(self::CAPTCHA_ENABLE, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
    }
    public function IS_NEWSLETTER()
    {
        if ($this->IS_ENABLE()) {
            return (bool)$this->scopeConfig->getValue(self::REC_NEWSLETTER, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
        } else {
            return false;
        }
    }
    public function IS_LOGINPAGE()
    {
       if ($this->IS_ENABLE()) {
            return (bool)$this->scopeConfig->getValue(self::CUST_LOGIN, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
        } else {
            return false;
        }
    }
    public function IS_CUSTREG()
    {
        if ($this->IS_ENABLE()) {
            return (bool)$this->scopeConfig->getValue(self::CUST_REG, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
        } else {
            return false;
        }
    }
    public function IS_PRODREVIEW()
    {
        if ($this->IS_ENABLE()) {
            return (bool)$this->scopeConfig->getValue(self::PROD_REVIEW, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
        } else {
            return false;
        }

    }
    public function IS_CHECKOUTREGISTER()
    {
        if ($this->IS_ENABLE()) {
            return (bool)$this->scopeConfig->getValue(self::CHECKOUTREGISTER, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
        } else {
            return false;
        }
    }
    public function IS_CONTACTUS()
    {
        if ($this->IS_ENABLE()) {
            return (bool)$this->scopeConfig->getValue(self::CONTACTUS, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
        } else {
            return false;
        }
    }
    public function IS_FORGOTPASSWORD()
    {
        if ($this->IS_ENABLE()) {
            return (bool)$this->scopeConfig->getValue(self::FORGOTPASSWORD, ScopeInterface::SCOPE_STORE,$this->getCurrentStoreInfo());
        } else {
            return false;
        }
    }
    public function getCaptchaverify($data)
    {
        if ($data['g-recaptcha-response'] != "") {
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $this->getSecretkey() . "&response=" . $data['g-recaptcha-response'] . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
            $googleobj = json_decode($response);

            $verified = $googleobj->success;
            return $verified;
        } else {
            return false;
        }
    }
    public function getNewsletterTemplate()
    {
        if($this->IS_NEWSLETTER())
        {
            return "Magecomp_Recaptcha::subscribe.phtml";
        }
        else
        {
            return "Magento_Newsletter::subscribe.phtml";
        }
    }
    public function getLoginTemplate()
    {
        if($this->IS_LOGINPAGE())
        {
            return "Magecomp_Recaptcha::customerlogin.phtml";
        }
        else
        {
            return "Magento_Customer::form/login.phtml";
        }
    }
    public function getForgotPasswordTemplate()
    {
        if($this->IS_FORGOTPASSWORD())
        {
            return "Magecomp_Recaptcha::customer/forgotpassword.phtml";
        }
        else
        {
            return "";
        }
    }
    public function getRegisterTemplate()
    {
        if($this->IS_CUSTREG())
        {
            return "Magecomp_Recaptcha::customer/form/register.phtml";
        }
        else
        {
            return "";
        }
    }
    public function getContactTemplate()
    {
        if($this->IS_CONTACTUS())
        {
            return "Magecomp_Recaptcha::form.phtml";
        }
        else
        {
            return "";
        }
    }
    public function getCheckoutRegistrationSuccessTemplate()
    {
        if($this->IS_CHECKOUTREGISTER())
        {
            return "Magecomp_Recaptcha::registration.phtml";
        }
        else
        {
            return "Magento_Checkout::registration.phtml";
        }
    }
    public function getProductReviewTemplate()
    {
        if($this->IS_PRODREVIEW())
        {
            return "Magecomp_Recaptcha::catalog/product/review/form.phtml";
        }
        else
        {
            return "Magento_Review::form.phtml";
        }
    }
}