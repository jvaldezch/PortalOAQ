<?php

require 'tcpdf.php';

class Pedimento extends TCPDF {

    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

        $this->invoiceData = $data;
        $this->SetFont('helvetica', 'C', 10);
        # Set the page margins: 72pt on each side, 36pt on top/bottom.
        $this->SetMargins(25, 26, 25, true);
        $this->SetAutoPageBreak(true, 26);

        # Set document meta-information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle($this->invoiceData["aduana"] . '-' . $this->invoiceData["patente"] . '-' . $this->invoiceData["pedimento"]);
        $this->SetSubject("");
        $this->SetKeywords("");

        //set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        global $l;
        $this->setLanguageArray($l);
    }

    public function Header() {

        $this->SetFont('helvetica', 'C', 7);
        $this->SetY(20, true);
        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
    <tr>
        <td>CLIENTE: " . $this->invoiceData["rfcCliente"] . "</td>
        <td style=\"text-align:center;\">USUARIO:" . $this->invoiceData["usuario"] . "</td>
        <td style=\"text-align:right;\">" . $this->invoiceData["fechaPago"] . "</td>
    </tr>
</table>";
        $this->writeHTML($tbl, true, false, false, false, '');
        $this->SetLineStyle(array('width' => 0.5, 'color' => $this->invoiceData["colors"]["line"]));
        $this->Line(25, 30, $this->getPageWidth() - 25, 30);
    }

    public function Footer() {
        $this->SetLineStyle(array('width' => 0.5, 'color' => $this->invoiceData["colors"]["line"]));
        $this->Line(25, $this->getPageHeight() - 35, $this->getPageWidth() - 25, $this->getPageHeight() - 35);
        $this->SetFont('helvetica', '', 7);
        $this->SetY(-33, true);

        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
    <tr>
        <td>" . $this->invoiceData["copia"] . "</td>
        <td style=\"text-align:center;\">" . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . "</td>
        <td style=\"text-align:right;\">" . $this->invoiceData["destino"] . "</td>
    </tr>
</table>";
        $this->writeHTML($tbl, true, false, false, false, '');
    }

    public function CreateInvoice() {
        $this->AddPage();
        $this->SetFont('helvetica', '', 7);
        $this->SetY(34, true);

        $th = "padding: 2px; border: 1px #999 solid; background-color: #f1f1f1;";
        $td = "padding: 2px; border: 1px #999 solid;";

        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"\" >
    <tr>
        <td style=\"{$th} width: 550px;\"><table>"
                . "<tr>"
                . "<td></td>"
                . "<td style=\"text-align:center;\">PEDIMENTO</td>"
                . "<td style=\"text-align:right;\">Ref: " . $this->invoiceData["referencia"] . "</td>
            </tr>
            </table></td>
        <td style=\"{$td} width: 150px;\"><table>"
                . "<tr>"
                . "<td style=\"text-align:right;\">Página:</td>"
                . "<td style=\"text-align:left;\">" . trim($this->getAliasNumPage()) . ' de ' . trim($this->getAliasNbPages()) . "</td>
            </tr>
            </table></td>
    </tr>
    <tr>
        <td style=\"{$td}\"><table>"
                . "<tr>"
                . "<td style=\"width: 80px;\">NUM.PEDIMENTO:</td>"
                . "<td style=\"width: 160px;\">" . substr($this->invoiceData["aduana"],0,2) . ' ' . $this->invoiceData["patente"] . ' ' . $this->invoiceData["pedimento"] . "</td>"
                . "<td style=\"width: 50px;\">T.OPER.:</td>"
                . "<td style=\"width: 40px;\">" . $this->invoiceData["tipoOp"] . "</td>"
                . "<td style=\"width: 80px;\">CVE.PEDIMENTO:</td>"
                . "<td style=\"width: 40px;\">" . $this->invoiceData["cvePed"] . "</td>"
                . "<td style=\"width: 50px;\">REGIMEN:</td>"
                . "<td style=\"width: 40px;\">" . $this->invoiceData["regimen"] . "</td>
            </tr>
            </table></td>
        <td style=\"{$th} text-align:center;\">CERTIFICACIONES</td>
    </tr>
</table>";
        $this->writeHTML($tbl, true, false, false, false, '');


//        # Table parameters
//        #
//        # Column size, wide (description) column, table indent, row height.
//        $col = 72;
//        $wideCol = 3 * $col;
//        $indent = ( $this->getPageWidth() - 2 * 72 - $wideCol - 3 * $col ) / 2;
//        $line = 18;
//
//        # Table header
//        $this->SetFont('', 'b');
//        $this->Cell($indent);
//        $this->Cell($wideCol, $line, 'Item', 1, 0, 'L');
//        $this->Cell($col, $line, 'Quantity', 1, 0, 'R');
//        $this->Cell($col, $line, 'Price', 1, 0, 'R');
//        $this->Cell($col, $line, 'Cost', 1, 0, 'R');
//        $this->Ln();
//        # Table content rows
//        $this->SetFont('', '');
//        foreach ($this->invoiceData['items'] as $item) {
//            $this->Cell($indent);
//            $this->Cell($wideCol, $line, $item[0], 1, 0, 'L');
//            $this->Cell($col, $line, $item[1], 1, 0, 'R');
//            $this->Cell($col, $line, $item[2], 1, 0, 'R');
//            $this->Cell($col, $line, $item[3], 1, 0, 'R');
//            $this->Ln();
//        }
//
//        # Table Total row
//        $this->SetFont('', 'b');
//        $this->Cell($indent);
//        $this->Cell($wideCol + $col * 2, $line, 'Total:', 1, 0, 'R');
//        $this->SetFont('', '');
//        $this->Cell($col, $line, $this->invoiceData['total'], 1, 0, 'R');

        $style = array(
            'border' => 0.5,
            'vpadding' => 1,
            'hpadding' => 1,
            'fgcolor' => $this->invoiceData["colors"]["line"],
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 2, // width of a single module in points
            'module_height' => 1  // height of a single module in points
        );
        $pedimento = $this->invoiceData["patente"] . "\n"                           // 1
                . $this->invoiceData["pedimento"] . "\n"                            // 2
                . str_pad($this->invoiceData["cvePed"], 3, '0', STR_PAD_LEFT) . "\n"        // 3
                . str_pad($this->invoiceData["rfcCliente"], 13, '0', STR_PAD_LEFT) . "\n"   // 4
                . str_pad("0", 13, '0', STR_PAD_LEFT) . "\n"                        // 5
                . str_pad("XYZHRL9A", 8, '0', STR_PAD_LEFT) . "\n"                  // 6
                . str_pad("440.000", 15, '0', STR_PAD_LEFT) . "\n"                  // 7
                . str_pad("3625", 12, '0', STR_PAD_LEFT) . "\n"                     // 8
                . str_pad("0", 12, '0', STR_PAD_LEFT) . "\n"                        // 9
                . str_pad("281", 13, '0', STR_PAD_LEFT) . "\n"                      // 10
                . str_pad("0", 4, '0', STR_PAD_LEFT) . "\n"                         // 11
                . "0";                                                              // 12
        $this->write2DBarcode($pedimento, 'PDF417', 180, 200, 0, 60, $style, 'N');
        $this->Text(180, 190, 'PED: ' . $this->invoiceData["aduana"] . ' ' . $this->invoiceData["patente"] . ' ' . $this->invoiceData["pedimento"] . ' ' . $this->invoiceData["cvePed"] . ' ' . $this->invoiceData["rfcCliente"]);
    }

}
