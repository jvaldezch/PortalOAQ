<?php

class Zend_View_Helper_FileExists extends Zend_View_Helper_Abstract
{
    //protected $_config;
    
    public function fileExists($filepath,$id,$filename)
    {
        //$this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        
        if(file_exists($filepath)) {
            if(preg_match('/.xml$/i', $filepath)) {            
                return '<a href="/archivo/index/read-file?id='.$id.'&tipo=xml">'.str_replace('.xml','',$filename).'</a>';
            } else {
                return '<a href="/archivo/index/read-file?id='.$id.'&tipo=pdf">'.str_replace('.xml','',$filename).'</a>';
            }
        }
        return "n/d";
    }
}
