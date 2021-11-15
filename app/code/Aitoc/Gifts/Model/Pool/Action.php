<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Model\Pool;

class Action
{
    /**
     * @var array
     */
    protected $actions;

    /**
     * Action constructor.
     *
     * @param array $actions
     */
    public function __construct(
        array $actions
    ) {
        $this->actions = $actions;
    }

    /**
     * @param bool $sorted
     *
     * @return array
     */
    public function getActions($sorted = true)
    {
        return $sorted ? $this->actionSort($this->actions) : $this->actions;
    }

    /**
     * @param $actions
     *
     * @return array
     */
    public function actionSort($actions)
    {
        $cmp = function ($a, $b) {
            $sortA = isset($a['sort_order']) ? $a['sort_order'] : 0;
            $sortB = isset($b['sort_order']) ? $b['sort_order'] : 0;
            if ($sortA == $sortB) {
                return 0;
            }

            return ($sortA < $sortB) ? -1 : 1;
        };
        uasort($actions, $cmp);

        return $actions;
    }

    /**
     * @param $actionCode
     *
     * @return mixed|null
     */
    public function getActionByCode($actionCode)
    {
        return isset($this->actions[$actionCode]) ? $this->actions[$actionCode] : null;
    }
}
