<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model;

class Date
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * Date constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->dateTime = $dateTime;
        $this->date = $date;
    }

    /**
     * @param bool $includedTime
     *
     * @return null|string
     */
    public function getCurrentDate($includedTime = true)
    {
        return $this->dateTime->formatDate($this->date->gmtTimestamp(), $includedTime);
    }

    /**
     * @param      $stringTime
     * @param bool $includedTime
     *
     * @return null|string
     */
    public function getDateFromString($stringTime, $includedTime = true)
    {
        return $this->dateTime->formatDate($this->dateTime->strToTime($stringTime), $includedTime);
    }

    /**
     * @param $stringTime
     *
     * @return int
     */
    public function getTimestampFromString($stringTime)
    {
        return $this->dateTime->strToTime($stringTime);
    }

    /**
     * @param      $days
     * @param bool $seconds
     *
     * @return int|null|string
     */
    public function getCurrentDateAfterDays($days, $seconds = false)
    {
        $timestamp = $this->date->gmtTimestamp() + ($days * 24 * 3600);

       return $seconds ? $timestamp : $this->dateTime->formatDate($timestamp);
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->date->gmtTimestamp();
    }
}
