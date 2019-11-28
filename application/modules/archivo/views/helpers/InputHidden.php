<?php

class Zend_View_Helper_InputHidden extends Zend_View_Helper_Abstract {

    public function inputHidden($type, $name, $value = null) {
        if(isset($value)) {
            return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\" value=\"{$value}\">";
        } else {
            return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$name}\">";
        }
    }

}
