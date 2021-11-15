<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Resolver;

interface ResolverInterface
{
    /**
     * @param string $type
     *
     * @return mixed
     */
    public function resolve($type);
}
