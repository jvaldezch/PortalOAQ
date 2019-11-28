<?php

class Zend_View_Helper_Eta extends Zend_View_Helper_Abstract {

    public function eta($date, $autorizada) {
        $myHelpers = new Application_View_Helper_MyHelpers();
        if ($autorizada == null) {
            $t1 = strtotime(date('Y-m-d H:i:s'));
            $t2 = strtotime($date);
            $diff = $t1 - $t2;
            $hours = round($diff / ( 60 * 60 ));
            if ((int) $hours >= -24 && (int) $hours <= 0) {
                return '<span class="traffic-eta-yellow">' . $myHelpers->dateSpanish($date) . '</span>';
            } else if ((int) $hours > 0) {
                return '<span class="traffic-eta-red">' . $myHelpers->dateSpanish($date) . '</span>';
            }
            return $myHelpers->dateSpanish($date);
        }
        return $myHelpers->dateSpanish($date);
    }

}
