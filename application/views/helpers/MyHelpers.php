<?php

class Application_View_Helper_MyHelpers extends Zend_View_Helper_Abstract {

    /**
     * Regresa fecha como dd-mes-year
     * 
     * @param string $value
     * @return string
     */
    public function dateSpanish($value) {
        $date = strtotime($value);
        $year = date("y", $date);
        $month = date("n", $date);
        $day = date("d", $date);
        $months = array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
        return $day . "-" . $months[(int)$month - 1] . "-" . $year;
    }

}
