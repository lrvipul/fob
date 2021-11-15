<?php

namespace Wyomind\PickupAtStore\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Wyomind\PickupAtStore\Helper\Data;
use Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\CollectionFactory;
/**
 * Class Calendar
 */
class Calendar extends \Magento\Backend\Block\Template
{
    /**
     * @var CollectionFactory
     */
    protected $posCollectionFactory;
    /**
     * Calendar constructor.
     * @param Context $context
     * @param CollectionFactory $posCollectionFactory
     * @param array $data
     */
    public function __construct(Context $context, CollectionFactory $posCollectionFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->posCollectionFactory = $posCollectionFactory;
    }
    public function getPosLegend()
    {
        $data = [];
        $i = 0;
        foreach ($this->posCollectionFactory->create() as $pos) {
            if (empty($pos->getCalendarColor())) {
                $color = Data::$calendarColors[$i++];
                if ($i >= count(Data::$calendarColors)) {
                    $i = 0;
                }
            } else {
                $color = $pos->getCalendarColor();
            }
            $data[$pos->getId()] = ["label" => $pos->getName(), "color" => $color];
        }
        return $data;
    }
}