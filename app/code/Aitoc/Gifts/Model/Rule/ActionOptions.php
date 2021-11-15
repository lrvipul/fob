<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Rule;

class Actionoptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Aitoc\Gifts\Model\Pool\Action
     */
    private $actionPool;

    public function __construct(
        \Aitoc\Gifts\Model\Pool\Action $actionPool
    ) {
        $this->actionPool = $actionPool;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->actionPool->getActions() as $code => $action) {
            $result[] = [
                'value' => $code,
                'label' => $action['title'],
            ];
        }

        return $result;
    }
}
