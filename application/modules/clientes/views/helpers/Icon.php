<?php

class Zend_View_Helper_Icon extends Zend_View_Helper_Abstract
{
    public function icon($filename)
    {
        if(preg_match('/.pdf$/i',$filename)) {
            return $this->img('pdf-icon.png');
        }
        if(preg_match('/.xls$/i',$filename) || preg_match('/.xlsx$/i',$filename)) {
            return $this->img('ms-excel.png');
        }
        if(preg_match('/.doc$/i',$filename) || preg_match('/.docx$/i',$filename)) {
            return $this->img('word-icon.png');
        }
        if(preg_match('/.xml$/i',$filename)) {
            return $this->img('xml-icon.png');
        }
        if(preg_match('/.zip$/i',$filename)) {
            return $this->img('zip-icon.png');
        }
        return '&nbsp;';
    }
    
    protected function img($icon)
    {
        return '<img src="/images/icons/'.$icon.'" border="0" style="margin: 0 7px" />';
    }
}
