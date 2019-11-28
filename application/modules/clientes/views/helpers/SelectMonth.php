<?php

class Zend_View_Helper_SelectMonth extends Zend_View_Helper_Abstract {

    public function selectMonth($id) {
        $array = array(
            "1" => "Enero",
            "2" => "Febrero",
            "3" => "Marzo",
            "4" => "Abril",
            "5" => "Mayo",
            "6" => "Junio",
            "7" => "Julio",
            "8" => "Agosto",
            "9" => "Septiembre",
            "10" => "Octubre",
            "11" => "Noviembre",
            "12" => "Diciembre",
        );
        $html = new V2_Html();
        $html->select("traffic-select-small", $id);
        foreach ($array as $k => $v) {
            if ((int) $k == ((int) date("m") - 1) && $k > 1) {
                $html->addSelectOption($k, $v, true);
            } else {
                $html->addSelectOption($k, $v);
            }
        }
        return $html->getHtml();
    }

}
