<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Resolver;

class ActionResolver implements ResolverInterface
{
    /**
     * @var \Aitoc\Gifts\Model\Pool\Action
     */
    private $actionPool;

    /**
     * ActionResolver constructor.
     *
     * @param \Aitoc\Gifts\Model\Pool\Action $actionPool
     */
    public function __construct(
        \Aitoc\Gifts\Model\Pool\Action $actionPool
    ) {
        $this->actionPool = $actionPool;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function resolve($type)
    {
        $actionData = $this->actionPool->getActionByCode($type);

        if ($actionData) {
            return $actionData['action_processor'];
        }

        return false;
    }
}
