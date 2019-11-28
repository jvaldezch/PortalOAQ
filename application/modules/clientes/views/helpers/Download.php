<?php

class Zend_View_Helper_Download extends Zend_View_Helper_Abstract
{
    public function download($id,$filename)
    {
        $misc = new OAQ_Misc();
        if(preg_match('/.pdf$/i',$filename)) {
            $encrypt = urlencode($misc->myEncrypt($id));
            return "<a href=\"/archivo/index/load-file?id={$encrypt}\" class=\"openpdf\"><i class=\"icon-eye-open\"></i></a>";
        } else {
            return '&nbsp;';
        } 
    }
}
