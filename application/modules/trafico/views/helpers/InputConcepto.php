<?php

class Zend_View_Helper_InputConcepto extends Zend_View_Helper_Abstract {

    public function inputConcepto($name, $tabindex, $value = null) {
        if (isset($value)) {
            if ($value != '' && $value != 0) {
                return "<input type=\"text\" name=\"conceptos[{$name}]\" id=\"{$name}\" value=\"" . number_format($value, 2, '.', ',') . "\" class=\"input-concepto\" tabindex=\"{$tabindex}\">";
            } else {
                return "<input type=\"text\" name=\"conceptos[{$name}]\" id=\"{$name}\" value=\"" . number_format(0, 2, '.', ',') . "\" class=\"input-concepto\" tabindex=\"{$tabindex}\">";
            }
        } else {
            return "<input type=\"text\" name=\"conceptos[{$name}]\" id=\"{$name}\" value=\"" . number_format(0, 2, '.', ',') . "\" class=\"input-concepto\" tabindex=\"{$tabindex}\">";
        }
    }

}
