<?php

require "tcpdf.php";

class MYPDFBACKGROUND extends TCPDF {

    // Page footer
    public function Footer() {
        $this->SetY(-20);
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        // Set font
        $this->SetFont('pdfacourier', false, 8);
        $foot = "---------------------------------------------------------------------------" . "\n" . $GLOBALS['addr1'] . "\n" . $GLOBALS['addr2'] . "\n [ Página " . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . ' ]';
        $this->MultiCell(0, 10, $foot, 0, 'C');
    }

}
