<?php


namespace XiangYu2038\Wish\Condition;

class XyLike extends XyCondition
{
    public function __construct($expressions=[])
    {
        parent::__construct($expressions);
        $this ->setType('like');
    }
    public function builder(){
        $expression =  $this -> getExpressions();
        $condition = [];
        foreach ($expression as $key=> $v){
            if($v instanceof XyConditionInterface){
                $condition[] =$v ->builder();
                continue;
            }
            if($this ->getFilter()){
                if($v){
                    $c = [$key,$v];
                     array_unshift($c,$this -> getType());
                    $condition[] = $c;
                }
            }else{
                $c = [$key,$v];
                $condition[] = $c;

            }
        }

        if(!$condition){
            return $condition;
        }
        array_unshift($condition,'and');
        return $condition;
    }

}
