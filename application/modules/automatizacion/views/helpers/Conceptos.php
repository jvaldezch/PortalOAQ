<?php

class Zend_View_Helper_Conceptos extends Zend_View_Helper_Abstract
{
    public function conceptos(&$array, $key)
    {
        foreach($array as $con) {
            if($con["descripcion"] == $key) {
                return "<td>{$con["valor_unitario"]}</td>";
            }
        }
        return "<td>&nbsp;</td>";
    }
}
