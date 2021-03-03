<?php

require 'tcpdf.php';

class Trafico extends TCPDF
{

    function __construct($data, $orientation, $unit, $format)
    {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

        $this->invoiceData = $data;
        $this->SetFont('helvetica', 'C', 10);
        $this->SetMargins(55, 26, 55, true);
        $this->SetAutoPageBreak(true, 26);
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle('SOLICITUD ' . $this->invoiceData["header"]["aduana"] . '-' . $this->invoiceData["header"]["patente"] . '-' . $this->invoiceData["header"]["pedimento"] . ' ' . $this->invoiceData["header"]["referencia"] . ' ' . $this->invoiceData["header"]["id"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        global $l;
        $this->setLanguageArray($l);
    }

    public function Header()
    {

        $this->SetFont('helvetica', 'C', 7);
        $this->SetY(20, true);
        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
    <tr>
        <td style=\"text-align:left;\">USUARIO: " . strtoupper($this->invoiceData["header"]["nombre"]) . "</td>
        <td style=\"text-align:center;\">" . $this->invoiceData["header"]["aduana"] . '-' . $this->invoiceData["header"]["patente"] . '-' . $this->invoiceData["header"]["pedimento"] . "</td>
        <td style=\"text-align:right;\">" . date('d/m/Y H:i:s a') . "</td>
    </tr>
</table>";
        $this->writeHTML($tbl, true, false, false, false, '');
        $this->SetLineStyle(array('width' => 0.5, 'color' => $this->invoiceData["colors"]["line"]));
        $this->Line(55, 30, $this->getPageWidth() - 55, 30);
    }

    public function Footer()
    {
        $this->SetLineStyle(array('width' => 0.5, 'color' => $this->invoiceData["colors"]["line"]));
        $this->Line(55, $this->getPageHeight() - 35, $this->getPageWidth() - 55, $this->getPageHeight() - 35);
        $this->SetFont('helvetica', '', 7);
        $this->SetY(-33, true);

        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
    <tr>
        <td></td>
        <td style=\"text-align:center;\">" . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . "</td>
        <td style=\"text-align:right;\"></td>
    </tr>
</table>";
        $this->writeHTML($tbl, true, false, false, false, '');
    }

    public function SolicitudAnticipo()
    {
        $this->AddPage();
        $this->SetFont('helvetica', '', 7);
        $this->SetY(40, true);

        $gf = array(220, 220, 220);
        $wf = array(255, 255, 255);
        $tc = array(20, 20, 20);

        $this->Image('images/logo_oaq.jpg', 56, 43, 100, 40, 'JPG', 'http://www.oaq.com.mx', '', true, 150, '', false, false, 0, false, false, false);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->SetTextColor($tc[0], $tc[1], $tc[2]);
        $this->MultiCell(100, 0, "", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        $this->SetFillColor($wf[0], $wf[1], $wf[2]);

        $dir = "ORGANIZACIÓN ADUANAL DE QUERÉTARO\nRFC: OAQ030623UL8\nC. Primer Retorno Blvd. Universitario No. 1\nCondominio Terra Business Park 43B, Col. La Pradera\nEl Marqués, Queretaro. C.P. 76269 Tel. 442-2160870";

        $this->Cell(15);
        $this->MultiCell(240, 0, $dir, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->Cell(15);
        $this->MultiCell(130, 15, "SOLICITUD ANTICIPO", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->Cell(370);
        $this->MultiCell(60, 15, "NUMERO:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(70, 15, $this->invoiceData["header"]["id"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->Cell(370);
        $this->MultiCell(60, 15, "FECHA:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(70, 15, date('d/m/Y', strtotime($this->invoiceData["header"]["creado"])), 1, 'L', 1, 0, '', '', true, 0, false, true, 0);

        $this->Ln(30);
        $tbl = '<p style="text-align: justify; line-height: 11px;">A CONTINUACIÓN SE DETALLA LA <strong>SOLICITUD DE ANTICIPO</strong> PARA IMPUESTOS Y GASTOS DETERMINADOS <strong>PROVISIONAL</strong>, MISMA QUE PODRÁ ESTAR SUJETA A CAMBIO, EL CUAL SERÁ NOTIFICADO OPORTUNAMENTE. ES INDISPENSABLE CONTAR CON EL IMPORTE TOTAL REQUERIDO ANTES DEL ARRIBO DEL EMBARQUE, PARA ESTAR EN POSIBILIDADES DE REALIZAR LOS PAGOS NECESARIOS DURANTE EL PROCESO DE DESPACHO Y EVITAR RETRASOS QUE PUEDAN GENERAR COSTOS EXTRAS.</p>';
        $this->writeHTML($tbl, true, false, false, false, '');

        $lineHeight = 10;
        $this->Ln(10);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(160, $lineHeight, "CLIENTE", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(60, $lineHeight, "REFERENCIA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(50, $lineHeight, "PESO", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(150, $lineHeight, "MERCANCIA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, $lineHeight, "VALOR (USD)", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $lineHeight = 24;
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(160, $lineHeight, $this->invoiceData["header"]["nombreCliente"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(60, $lineHeight, $this->invoiceData["header"]["referencia"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(50, $lineHeight, number_format($this->invoiceData["detalle"]["peso"], 2, '.', ','), 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(150, $lineHeight, $this->invoiceData["detalle"]["mercancia"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(80, $lineHeight, '$ ' . number_format($this->invoiceData["detalle"]["valorMercancia"], 2, '.', ','), 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $lineHeight = 12;
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(160, $lineHeight, "TIPO EMBARQUE", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(60, $lineHeight, "OPERACIÓN", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(50, $lineHeight, "CVE PED", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(115, $lineHeight, "ETA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(115, $lineHeight, "LIBRE ALMACENAJE", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(160, $lineHeight, $this->invoiceData["detalle"]["tipoCarga"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(60, $lineHeight, $this->invoiceData["header"]["tipoOperacion"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(50, $lineHeight, $this->invoiceData["detalle"]["cvePed"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(115, $lineHeight, date('d/m/Y', strtotime($this->invoiceData["detalle"]["fechaEta"])), 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(115, $lineHeight, date('d/m/Y', strtotime($this->invoiceData["detalle"]["fechaAlmacenaje"])), 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $lineHeight = 20;
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(160, $lineHeight, "FACTURA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, $lineHeight, "SE FACTURA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(75, $lineHeight, "PECA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(155, $lineHeight, "BL / GUIA / NUM.CONTENEDOR / PLACAS / CAJA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $lineHeight = 24;
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(160, $lineHeight, $this->invoiceData["detalle"]["numFactura"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(110, $lineHeight, $this->invoiceData["detalle"]["tipoFacturacion"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(75, $lineHeight, ($this->invoiceData["detalle"]["peca"] == '1') ? 'SI' : 'NO', 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(155, $lineHeight, $this->invoiceData["detalle"]["bl"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);

        $this->Ln();
        $lineHeight = 12;
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(270, $lineHeight, "MERCANCIA", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(230, $lineHeight, "ALMACEN", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $lineHeight = 24;
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(270, $lineHeight, $this->invoiceData["detalle"]["mercancia"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(230, $lineHeight, $this->invoiceData["detalle"]["almacen"], 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln(12);

        $col = array(
            'col1' => 190,
            'col2' => 60,
        );
        $lineHeight = 10;
        $this->Ln(20);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(500, $lineHeight, "COTIZACIÓN DE GASTOS PROVISIONAL SUJETA A CAMBIOS", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell($col["col1"], $lineHeight, "CONCEPTO", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($col["col2"], $lineHeight, "IMPORTE", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($col["col1"], $lineHeight, "CONCEPTO", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell($col["col2"], $lineHeight, "IMPORTE", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        foreach ($this->invoiceData["conceptos"] as $item) {
            $this->Ln();
            $this->MultiCell($col["col1"], $lineHeight, $item[0], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($col["col2"], $lineHeight, ($item[1] != 0) ? '$ ' . number_format($item[1], 2, '.', ',') : "", 1, 'R', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($col["col1"], $lineHeight, isset($item[2]) ? $item[2] : "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
            $this->MultiCell($col["col2"], $lineHeight, (isset($item[3]) && $item[3] != 0) ? '$ ' . number_format($item[3], 2, '.', ',') : "", 1, 'R', 1, 0, '', '', true, 0, false, true, 0);
        }
        $this->Ln();
        $this->Cell(250);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell($col["col1"], $lineHeight, "SUBTOTAL", 1, 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell($col["col2"], $lineHeight, '$ ' . number_format($this->invoiceData["total"], 2, '.', ','), 1, 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->Cell(250);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell($col["col1"], $lineHeight, "ANTICIPO", 1, 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell($col["col2"], $lineHeight, '$ ' . number_format($this->invoiceData["anticipo"], 2, '.', ','), 1, 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->Cell(250);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell($col["col1"], $lineHeight, "TOTAL REQUERIDO", 1, 'R', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell($col["col2"], $lineHeight, '$ ' . number_format(($this->invoiceData["total"] - $this->invoiceData["anticipo"]), 2, '.', ','), 1, 'R', 1, 0, '', '', true, 0, false, true, 0);

        $lineHeight = 10;
        $this->Ln(20);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(270, $lineHeight, "OBSERVACIONES", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(130, $lineHeight, "IMPORTE POR ANTICIPO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(270, $lineHeight * 2, "", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "SERV. EXTRAORDINARIO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->Cell(270);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "EVENTO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(100, $lineHeight, "FECHA", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(270, $lineHeight, "DATOS PARA TRANSFERENCIA O DEPÓSITO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "RECEPCIÓN DE ANTICIPO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(70, $lineHeight, "BANCO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(200, $lineHeight, $this->invoiceData["banco"]["nombre"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "RECEPCIÓN DE DOCTOS.", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(70, $lineHeight, "BENEFICIARIO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(200, $lineHeight, $this->invoiceData["banco"]["razonSocial"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "REVALIDACIÓN", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(70, $lineHeight, "CUENTA", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(200, $lineHeight, $this->invoiceData["banco"]["cuenta"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "PROGRAMACIÓN DE PREVIO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(70, $lineHeight, "SUCURSAL", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(200, $lineHeight, $this->invoiceData["banco"]["sucursal"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "REALIZACIÓN DE PREVIO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(70, $lineHeight, "CLABE", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(200, $lineHeight, $this->invoiceData["banco"]["clabe"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "PAGO DE PEDIMENTO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->MultiCell(270, $lineHeight * 2, "* No se aceptan depósitos en efectivo.\n** Confirmar depósito mediante comprobante por correo electrónico.", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "SOLICITUD DE MANIOBRA", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->Cell(270);
        $this->SetFillColor($gf[0], $gf[1], $gf[2]);
        $this->MultiCell(130, $lineHeight, "DESPACHO", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor($wf[0], $wf[1], $wf[2]);
        $this->MultiCell(100, $lineHeight, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);

        $this->Ln(15);
        $tbl = '<p style="text-align: justify; line-height: 9px; font-size: 8px">FACTORES QUE PUEDAN GENERAR CAMBIOS EN LO COTIZADO: CAMBIO DE FRACCIÓN ARANCELARÍA DERIVADA DEL RESULTADO DE LA REVISIÓN FÍSICA DE LA MERCANCIA, REQUERIMIENTO DE MANIOBRAS ESPECIALES Y LA FECHA EN QUE SE RECIBAN TODOS LOS ELEMENTOS NECESARIOS PARA EL DESAPACHO QUE SON DOCUMENTOS, PERMISOS, ETC.</p>';
        $tbl .= '<p style="text-align: justify; line-height: 9px; font-size: 8px">' . $this->invoiceData["header"]["empresa"] . ' NO SERÁ RESPONSABLE DE LOS <strong>ALMACENAJES Y DEMORAS</strong>, EN LOS CASOS EN QUE LA TERMINAL, NAVIERA NO CUENTE CON EL ESPACIO Y EQUIPO NECESARIO PARA LA PROGRAMACIÓN DE <strong>PREVIOS O MANIOBRAS</strong> DE CARGA, ETCETERA, ASÍ COMO CUANDO ALGUNA AUTORIDAD DENTRO DE SUS FACULTADES DETENGA EL EMBARQUE PARA REVISIÓN. LOS IMPORTES SEÑALADOS SON APROXIMADOS Y VARÍAN CONFORME AL COSTO SOPORTADO CON SU COMPROBANTE, CERRÁNDOSE LAS CIFRAS REALES AL MOMENTO DE ELABORAR LA CUENTA DE GASTOS.</p>';
        $this->writeHTML($tbl, true, false, false, false, '');

        $this->lastPage();
    }
}
