<?php


namespace XiangYu2038\Wish\Condition;

class XyAnd extends XyCondition
{
    public function __construct($expressions)
    {
        parent::__construct($expressions);
        $this ->setType('and');
    }
}
