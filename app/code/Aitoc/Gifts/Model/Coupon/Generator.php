<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Coupon;

use Magento\SalesRule\Model\Coupon\Massgenerator;

class Generator
{
    const COUPON_DELIMITER = '-';

    const DEFAULT_COUPON_EXPIRATION_DAYS = 10;

    /**
     * @var Massgenerator
     */
    protected $couponMassgenerator;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Collection
     */
    protected $couponCollection;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule
     */
    private $rule;

    /**
     * @var \Aitoc\Gifts\Model\CouponFactory
     */
    private $couponFactory;

    /**
     * Count of generated Coupons
     * @var int
     */
    private $generatedCount = 0;

    /**
     * @var array
     */
    private $generatedCodes = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\SalesRule\Helper\Coupon
     */
    private $salesRuleCoupon;

    /**
     * @var \Aitoc\Gifts\Model\Date
     */
    private $date;

    /**
     * Generator constructor.
     *
     * @param \Magento\SalesRule\Model\Coupon\MassgeneratorFactory            $couponMassgenerator
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory
     * @param \Aitoc\Gifts\Model\CouponFactory                               $couponFactory
     * @param \Magento\SalesRule\Model\Rule                                   $rule
     * @param \Magento\SalesRule\Helper\Coupon                                $salesRuleCoupon
     * @param \Aitoc\Gifts\Model\Date                                        $date
     */
    public function __construct(
        \Magento\SalesRule\Model\Coupon\MassgeneratorFactory $couponMassgenerator,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        \Aitoc\Gifts\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\SalesRule\Helper\Coupon $salesRuleCoupon,
        \Aitoc\Gifts\Model\Date $date
    ) {
        $this->couponMassgenerator = $couponMassgenerator;
        $this->couponCollection = $couponCollectionFactory->create();
        $this->rule = $rule;
        $this->couponFactory = $couponFactory;
        $this->salesRuleCoupon = $salesRuleCoupon;
        $this->date = $date;
    }

    /**
     * @param $generateData
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateCouponPool($generateData)
    {
        $this->generatedCount = 0;
        $this->generatedCodes = [];

        for ($i = 0; $i < $generateData['qty']; $i++) {
            $attempt = 0;

            do {
                if ($attempt >= Massgenerator::MAX_GENERATE_ATTEMPTS) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('We cannot create the requested Coupon Qty. Please check your settings and try again.')
                    );
                }
                $code = $this->generateCode($generateData);
                ++$attempt;
            } while ($this->checkExistCode($code));

            $expirationDate = $generateData['expiration_date']
                ? $this->date->getDateFromString($generateData['expiration_date'])
                : $this->date->getCurrentDateAfterDays(self::DEFAULT_COUPON_EXPIRATION_DAYS);

            $this->generatedCount += 1;

            $this->generatedCodes[] = [
                'rule_id' => $generateData['rule_id'],
                'coupon_code' => $code,
                'expiration_date' => $expirationDate,
                'created_at' => $this->date->getCurrentDate(),
            ];
        }
        $this->createNewCoupons($this->generatedCodes);

        return $this->generatedCodes;
    }

    /**
     * Generate coupon code
     *
     * @return string
     */
    public function generateCode($generateData)
    {
        $format = isset($generateData['format']) ? $generateData['format'] : false;
        if (empty($format)) {
            $format = \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC;
        }
        $charset = $this->salesRuleCoupon->getCharset($format);
        $code = '';
        $charsetSize = count($charset);
        $split = max(0, (int)$generateData['dash']);
        $length = max(1, (int)$generateData['length']);
        for ($i = 0; $i < $length; ++$i) {
            $char = $charset[\Magento\Framework\Math\Random::getRandomNumber(0, $charsetSize - 1)];
            if (($split > 0) && (($i % $split) === 0) && ($i !== 0)) {
                $char = self::COUPON_DELIMITER . $char;
            }
            $code .= $char;
        }

        return $code;
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function checkExistCode($code)
    {
        return $this->couponFactory->create()->getCouponByCode($code)->getId();
    }

    /**
     * @param array $couponData
     */
    private function createNewCoupons(array  $couponData)
    {
        foreach ($couponData as $item) {
            $coupon = $this->couponFactory->create();

            $coupon->setData($item)->save();
        }
    }
}
