<?php

class Zend_View_Helper_InputText extends Zend_View_Helper_Abstract {

    public function inputText($name, $value = null, $style = null) {
        if(isset($value)) {
            if($value != '' && $value != 0) {
                return "<input type=\"text\" name=\"{$name}\" id=\"{$name}\" value=\"{$value}\" style=\"{$style}\">";
            } else {
                return "<input type=\"text\" name=\"{$name}\" id=\"{$name}\" style=\"{$style}\">";                
            }
        } else {
            return "<input type=\"text\" name=\"{$name}\" id=\"{$name}\" style=\"{$style}\">";
        }
    }

}
