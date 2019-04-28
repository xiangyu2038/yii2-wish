<?php


namespace XiangYu2038\Wish\Condition;

class XyCondition implements XyConditionInterface
{
    private $expressions = [];
    private $type;
    private $fliter = true;
    private $fliter_flag = 'all';
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

    public function setFilterFlag($filter=true){
        $this->fliter_flag = $filter;
        return $this;
    }
    public function getFilterFlag(){
        return  $this->fliter_flag;
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
               ///TODO 不能过滤掉0
                if(!$this -> isEmpty($v)){
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

    protected function isEmpty($value)
    {
        return $value === $this ->fliter_flag||$value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }
}
