<?php
namespace Magecomp\Recaptcha\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Block\Template\Context;
use \Magento\Framework\Message\ManagerInterface;
use Magecomp\Recaptcha\Helper\Data as HelperData;

class Recaptcha extends \Magento\Framework\View\Element\Template
{
	protected $_objectManager;
	protected $messageManager;
	protected $_store;
	protected $_helperData;
	public function __construct(
		ObjectManagerInterface $objectManager,
		Context $context,
		ManagerInterface $messageManager,
		HelperData $helperData
	){
		$this->_objectManager = $objectManager;
		$this->messageManager = $messageManager;
		$this->_helperData = $helperData;
		parent::__construct($context);
	}
	public function getLocal()
	{
		return $this->_helperData->getLocal();
	}
	public function contactSuccessMsg()
	{
		$this->messageManager->addSuccess('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.');
	}

	public function contactFailMsg()
	{
		$this->messageManager->addError('We can\'t process your request right now. Sorry, that\'s all we know.');
	}

	public function contactInvalidCaptchaMsg()
	{
		$this->messageManager->addError('Please Enter Valid Captcha.');
	}
	public function isEnable()
	{
		return $this->_helperData->IS_ENABLE();
	}
	public function getSiteKey()
	{
		return $this->_helperData->getSiteKey();
	}

	public function getThemeOption()
	{
		return $this->_helperData->getThemeOption1();
	}

}