<?php


namespace XiangYu2038\Wish\Condition;

class XyCondition implements XyConditionInterface
{
    private $expressions = [];
    private $type;
    private $fliter = true;
    public function __construct($expressions)
    {
        $this->expressions = $expressions;
    }
    public function getExpressions()
    {
        return $this->expressions;
    }
    public function setExpressions($expressions)
    {
      $this -> expressions = array_merge($this -> expressions,$expressions);
      return $this;
    }

    public function getType(){
        return $this->type;
    }
    public function setType($type){
        $this->type = $type;
        return $this;
    }
    public function setFilter($filter=true){
        $this->fliter = $filter;
        return $this;
    }
    public function getFilter(){
       return  $this->fliter;
    }
    public function builder(){
        $expression =  $this -> getExpressions();
        $condition = [];
        foreach ($expression as $key=> $v){
            if($v instanceof XyConditionInterface){
                $condition[] =$v ->builder();
                continue;
            }
           if($this ->fliter){
                if($v){
                    $condition[][$key] =$v;
                }
           }else{
               $condition[][$key] =$v;
           }
        }
        if(!$condition){
            return $condition;
        }
        array_unshift($condition,$this -> type);
        return $condition;
    }
}
