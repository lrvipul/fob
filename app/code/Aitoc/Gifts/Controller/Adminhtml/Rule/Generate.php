<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Controller\Adminhtml\Rule;

class Generate extends \Aitoc\Gifts\Controller\Adminhtml\Rule
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    private $dateFilter;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $dataJson;

    /**
     * @var \Aitoc\Gifts\Model\Coupon\Generator
     */
    private $generator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Aitoc\Gifts\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Json\Helper\Data $dataJson,
        \Aitoc\Gifts\Model\Coupon\Generator $generator
    ) {
        parent::__construct($context, $ruleFactory, $registry, $resultPageFactory);
        $this->dateFilter = $dateFilter;
        $this->dataJson = $dataJson;
        $this->generator = $generator;
    }

    /**
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }

        $result = [];
        $rule = $this->getRule();

        if (!$rule->getId()) {
            $result['error'] = __('Rule is not defined');
        } else {
            try {
                $data = $this->getRequest()->getParams();
                $data['expiration_date'] = $rule->getData('to_date');
                $couponCodes = $this->generator->generateCouponPool($data);
                $this->messageManager->addSuccessMessage(__('%1 coupon(s) have been generated.', count($couponCodes)));
                $this->_view->getLayout()->initMessages();
                $result['messages'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
            } catch (\Magento\Framework\Exception\InputException $inputException) {
                $result['error'] = __('Invalid data provided');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['error'] = $e->getMessage();
            } catch (\Exception $e) {
                $result['error'] = __(
                    'Something went wrong while generating coupons. Please review the log and try again.'
                );
            }
        }

        $this->getResponse()->representJson($this->dataJson->jsonEncode($result));
    }
}
