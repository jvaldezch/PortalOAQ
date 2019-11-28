<?php
require 'tcpdf.php';
class MYPDF extends TCPDF {

    public function Footer() {
        $appconfig = new Application_Model_ConfigMapper();
        $this->SetY(-20);        
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
        $this->SetFont('helvetica', false, 8);
        $foot = $appconfig->getParam('footer-edoc-addr1') . "\n" . $appconfig->getParam('footer-edoc-addr2') . "\n [ Página ".$this->getAliasNumPage().'/'.$this->getAliasNbPages() . ' ]';
        $this->MultiCell(0, 10, $foot, 0, 'C');        
    }
}