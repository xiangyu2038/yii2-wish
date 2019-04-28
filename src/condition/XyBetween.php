<?php


namespace XiangYu2038\Wish\Condition;

class XyBetween extends XyCondition
{
    public function __construct($expressions=[])
    {
        parent::__construct($expressions);
        $this ->setType('between');
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
                if($v[0]&&$v[1]){
                    array_unshift($v,$key);
                    array_unshift($v,$this -> getType());
                    $condition[] = $v;
                }
            }else{
                array_unshift($v,$key);
                array_unshift($v,$this -> getType());
                $condition[] = $v;

            }
        }


        if(!$condition){
            return $condition;
        }
        array_unshift($condition,'and');
        return $condition;
    }

}
