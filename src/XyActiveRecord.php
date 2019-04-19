<?php
namespace common\helpers;
trait XyActiveRecord  {
    public $hasRelation;
    public $add=[];
    public $delete=[];
    public $only = [];
    public $wish = [];
//////////////////////////////////////////
//    public static function populateRecord($record, $row)
//    {
//        $columns = static::getTableSchema()->columns;
//        //$row = $record -> xyRow($row);
//        foreach ($row as $name => $value) {
//            if (isset($columns[$name])) {
//                $row[$name] = $columns[$name]->phpTypecast($value);
//            }
//        }
//        parent::populateRecord($record, $row);
////////////////////////////////////////////////////////////////
//    }




//    public function attributes()
//    {
//
//        $attributes  =parent::attributes();
//        return  array_merge($attributes,$this -> add);
//    }

/////////////////////////////////////////////////
//    public function xyRow(){
//        $add_row = $this->addRow();
//        if($add_row){
//            BaseActiveRecord::populateRecord($this, $add_row);
//        }
//
//
////        $delete_row = $this->deleteRow();
////        array_walk($delete_row, function ($value) use (&$row) {
////            unset($row[$value]);
////        });
//
//
//        //return $row;
//
////        if (!$only_row) {
////            return $row;
////        }
////        $res_row = [];
////
////        foreach ($only_row as $v){
////            $res_row[$v] = $row[$v];
////        }
////
////        return $res_row;
//    }

    public function addRow(){
        $add_row = [];
        foreach ($this -> add as $add){
            $add_row[$add] = $add;
        }
        return $add_row;
    }

    public function deleteRow(){
        return $this -> delete;
    }

    public function onlyRow(){
    return $this -> only;
}

    public function wish(){
        return $this -> wish;
    }

    public function fields()
    {
        $only_row = $this->onlyRow();
        if ($only_row) {
            return $only_row;
        }
        $res = parent::fields();
        if ($this->hasRelation) {
            $res = array_merge($res, $this->extraFields());
        }
        if ($add_row = $this->addRow()) {
            $res = array_merge($res, $add_row);
        }

        if($wishs = $this -> wish()){
             foreach ($wishs as $wish){
                 $f = 'get' . ucfirst($this->convertUnderline($wish));
                 $this -> $wish = $this -> $f();
             }
        }
        if ($delete_row = $this->deleteRow()) {
            foreach ($delete_row as $delete) {
                unset($res[$delete]);
            }
        }
        return $res;
    }
    public function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }

}