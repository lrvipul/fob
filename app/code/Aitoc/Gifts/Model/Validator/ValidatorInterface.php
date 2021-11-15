<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Validator;

interface ValidatorInterface
{
    /**
     * @param $object
     * @param $quote
     *
     * @return $object
     */
    public function validate($object, $quote);
}
