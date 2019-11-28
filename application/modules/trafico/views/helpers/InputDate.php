<?php

class Zend_View_Helper_InputDate extends Zend_View_Helper_Abstract {

    public function inputDate($type, $name, $value, $class) {
        if (isset($value)) {
            if ($value != '' && $value != '0000-00-00 00:00:00') {
                return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\" value=\"" . date("Y-m-d", strtotime($value)) . "\" class=\"{$class}\" style=\"text-align: center; width: 80px\">";
            } else {
                return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\" value=\"\" class=\"{$class}\" style=\"text-align: center; width: 80px\">";
            }
        } else {
            return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\" value=\"\" class=\"{$class}\" style=\"text-align: center; width: 80px\">";
        }
    }

}
