<?php


namespace XiangYu2038\Wish\Condition;

class XyLike extends XyCondition
{
    public function __construct($expressions)
    {
        parent::__construct($expressions);
        $this ->setType('like');
    }
}
