<?php

class Zend_View_Helper_Trim extends Zend_View_Helper_Abstract
{
    public function trim($filename)
    {
        if(strlen($filename) > 32) {
            if(preg_match('/.pdf$/i',$filename)) {
                return substr($filename,0,32) . '[...].pdf';
            }
            if(preg_match('/.xls$/i',$filename)) {
                return substr($filename,0,32) . '[...].xls';
            }
            if(preg_match('/.doc$/i',$filename)) {
                return substr($filename,0,32) . '[...].doc';
            }
            if(preg_match('/.xml$/i',$filename)) {
                return substr($filename,0,32) . '[...].xml';
            }
            if(preg_match('/.zip$/i',$filename)) {
                return substr($filename,0,32) . '[...].zip';
            }
        } else {
            return $filename;
        }
    }
}
