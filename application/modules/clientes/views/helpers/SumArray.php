<?php

class Zend_View_Helper_SumArray extends Zend_View_Helper_Abstract {

    public function sumArray($array, $concepts) {
        $sum = 0;
        foreach ($concepts as $con) {
            if (isset($array[$con])) {
                $sum += $array[$con]["total"];
            }
        }
        return $sum;
    }

}
