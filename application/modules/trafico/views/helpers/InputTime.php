<?php

class Zend_View_Helper_InputTime extends Zend_View_Helper_Abstract {

    public function inputTime($type, $name, $value, $class) {
        if(isset($value)) {
            return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\" value=\"".date("H:i A",  strtotime($value))."\" class=\"{$class}\">";
        } else {
            return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\" value=\"\" class=\"{$class}\">";
        }
    }

}
