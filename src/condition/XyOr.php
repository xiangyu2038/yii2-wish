<?php


namespace XiangYu2038\Wish\Condition;

class XyOr extends XyCondition
{
    public function __construct($expressions)
    {
        parent::__construct($expressions);
        $this ->setType('or');
    }

}
