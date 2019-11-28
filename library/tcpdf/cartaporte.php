<?php

require 'tcpdf.php';

class CartaPorte extends TCPDF {

    function __construct($data, $orientation, $unit, $format) {
        parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

        $this->invoiceData = $data;
        $this->SetFont('helvetica', 'C', 10);
        # Set the page margins: 72pt on each side, 36pt on top/bottom.
        $this->SetMargins(55, 26, 55, true);
        $this->SetAutoPageBreak(true, 26);

        # Set document meta-information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor("Jaime E. Valdez");
        $this->SetTitle('CARTA_PORTE_' . $this->invoiceData["id"]);
        $this->SetSubject("");
        $this->SetKeywords("");
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        global $l;
        $this->setLanguageArray($l);
    }

    public function Header() {

        $this->SetFont('helvetica', 'C', 7);
        $this->SetY(20, true);
        $tbl = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
    <tr>
        <td>CLIENTE: " . $this->invoiceData["rfcCliente"] . "</td>
        <td style=\"text-align:center;\">USUARIO: " . strtoupper($this->invoiceData["creadoPor"]) . "</td>
        <td style=\"text-align:right;\">" . date('d/m/Y H:i:s a') . "</td>
    </tr>
</table>";
        $this->writeHTML($tbl, true, false, false, false, '');
        $this->SetLineStyle(array('width' => 0.5, 'color' => $this->invoiceData["colors"]["line"]));
        $this->Line(55, 30, $this->getPageWidth() - 55, 30);
    }

    public function Footer() {
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

    public function CreateLetter() {
        $this->AddPage();
        $this->SetFont('helvetica', '', 7);
        $this->SetY(54, true);
        
        $this->Image('images/oaq_transportes.jpg', 55, 55, 100, 40, 'JPG', 'http://www.tcpdf.org', '', true, 150, '', false, false, 0, false, false, false);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(100, 0, "", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->Cell(15);
        $this->MultiCell(240, 0, "OAQ TRANSPORTES DE QUERETARO, S.A. DE C.V.\n\nRFC: OTQ131120BW4\nMariano Perrusquia No. 102 int. 4 Col. San Angel\nQuerétaro, Queretaro. C.P. 76030 Tel. 442-2160533\nNextel ID 62*168729*9 Nextel No. (01 449)4411740\nRégimen Fiscal: Personas Morales Régimen General de Ley\nSERVICIO DE TRANSPORTE DE CARGA FEDERAL", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->Cell(15);
        $this->MultiCell(130, 15, "CARTA PORTE", 1, 'C', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln();
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->Cell(370);
        $this->MultiCell(60, 15, "FOLIO:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(70, 15, $this->invoiceData["id"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->Cell(370);
        $this->MultiCell(60, 15, "FECHA:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(70, 15, date('d/m/Y',  strtotime($this->invoiceData["fecha"])), 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln(55);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(100, 20, "CLIENTE:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell($this->getPageWidth() - 100 - 110, 20, $this->invoiceData["cliente"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(100, 20, "RFC:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell($this->getPageWidth() - 100 - 110, 20, $this->invoiceData["rfcCliente"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(100, 20, "DOMICILIO:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell($this->getPageWidth() - 100 - 110, 20, $this->invoiceData["domicilio"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln(30);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(100, 20, "ORIGEN:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(150, 20, $this->invoiceData["origen"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(100, 20, "DESTINO:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(151, 20, $this->invoiceData["destino"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(250, 40, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(251, 40, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln(50);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(70, 10, "CANTIDAD:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 10, "UNIDAD:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 10, "PESO:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(290, 10, "EL REMITENTE DICE QUE CONTIENE:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(70, 50, $this->invoiceData["cantidad"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 50, $this->invoiceData["unidad"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 50, $this->invoiceData["peso"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(290, 50, $this->invoiceData["mercancia"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln(60);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(70, 10, "REFERENCIA:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 10, "PEDIMENTO:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(140, 10, "PLACAS:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(220, 10, "OPERADOR:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(70, 50, $this->invoiceData["referencia"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(70, 50, $this->invoiceData["pedimento"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(140, 50, $this->invoiceData["placas"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(220, 50, $this->invoiceData["operador"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->Ln(60);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(150, 10, "DOCUMENTO:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(150, 10, "RECIBI DE CONFORMIDAD:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(200, 10, "OBSERVACIONES:", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Ln();
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(20, 20, 20);
        $this->MultiCell(150, 70, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(150, 70, "", 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->MultiCell(200, 70, $this->invoiceData["observaciones"], 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        
        $this->AddPage();
        $this->SetFont('helvetica', '', 8);
        $this->SetY(44, true);
        
        $tbl = '<p style="text-align: center; text-decoration: underline;">Condiciones del Contrato De Transporte Que Ampara Esta Carta Porte</p>
<p>PRIMERA: Para los efectos del presente contrato de transporte se denomina “porteador” al transportista y “remitente” al usuario que contrate el servicio.</p>
<p>SEGUNDA: El “remitente” es responsable de que la información proporcionada al “porteador” sea veraz y que la documentación que entregue para efectos de transporte sea la correcta.</p>
<p>TERCERA: El “remitente” debe declarar al “porteador” el tipo de mercancía o efectos de que se trate, peso, medidas y/o número de la  carga que entrega para su transporte y en su caso, el valor de la misma la carga que se entregue a granel será pesada por el “porteador” en el primer punto donde haya bascula apropiada o en su defecto aforada en metros cúbicos en la conformidad del “remitente”.</p>
<p>CUARTA: Para efectos del transporte el “remitente” deberá entregar al “porteador” los documentos que las leyes y reglamentos exijan para llevar a cabo el servicio en caso de no cumplirse con estos requisitos el porteador está obligado a rehusar el transporte de las mercancías.</p>
<p>QUINTA: Si por sospecha de falsedad en la declaración del contenido de un bulto el “porteador” deseara proceder a su reconocimiento podrá hacerlo ante testigos y con asistencia del “remitente” o del consignatario si este último no concurriere se solicitara la presencia de un inspector de la secretaria de comunicaciones y transportes y se levantara el acta correspondiente el “porteador” tendrá en todo caso la obligación de dejar los bultos en el estado en que se encontraron antes del reconocimiento.</p>
<p>SEXTA: El “porteador” deberá recoger y entregar la carta precisamente en los domicilios que señale el “remitente” ajustándose a los términos y condiciones convenidos el “porteador” solo  está obligado a llevar la carga al domicilio del consignatario para su entrega una sola vez si esta no fuera recibida se dejara aviso de que la mercancía queda a disposición del interesado en las bodegas que indique el “porteador”.</p>
<p>SEPTIMA: Si la carga no fuera retirada dentro de los 30 días siguientes a aquel en que hubiese sido puesta a disposición del consignatario el porteador podrá solicitar la venta en pública subasta  con arreglo a lo que dispone el código de comercio.</p>
<p>OCTAVA: El “porteador” y el “remitente” negociaran libremente el precio del servicio, tomando en cuenta su tipo, características de los embarques, volumen,  regularidad,  clase de carga y sistema de pago.</p>
<p>NOVENA: Si el “remitente” desea que el “porteador” asuma la responsabilidad por el valor de las mercancías o efectos que el declare y que cubra toda clase de riesgos, inclusive los derivados de caso fortuito o de naturaleza de fuerza mayor, las partes deberán convenir un cargo adicional, equivalente al valor de la prima del seguro que se contrate, el cual deberá expresar en la carta porte.</p>
<p>DECIMA: Cuando el importe del flete no incluya la carga adicional,  la responsabilidad del “porteador” queda expresamente limitada a la cantidad equivalente a 15 días del salario mínimo vigente del distrito federal por tonelada o cuando se trate de embarques cuyo peso sea mayor a 200 kg. Menor a 1000 kg. Y a 4 días de salario mínimo por remesa cuando se trate de embarques con peso hasta de 200 kg.</p>
<p>DECIMA PRIMERA: El precio del transporte deberá pagarse en origen, salvo convenio entre las partes de pago en destino, cuando el transporte se hubiere concertado “flete por cobrar” la entrega de las mercancías o efectos se hará contra el pago del flete y el “porteador” tendrá derecho a retenerlos mientras no se le cubra el precio convenido.</p>
<p>DECIMA SEGUNDA: Si al momento de la entrega resulta algún faltante o avería,  el consignatario deberá hacer constar en ese acto en la carta porte y formular su reclamación por escrito al “porteador” dentro de las 24 horas siguientes.</p>
<p>DECIMO TERCERA: El “porteador” queda eximido de la obligación de recibir mercancías o efectos para su transporte en los siguientes casos:</p>
<p>a.- cuando se trate de carga que por su  naturaleza,  peso,  volumen,  embalaje defectuoso o cualquier otra circunstancia no pueda transportase sin destruirse o sin causar daño a los demás artículos o al material rodante,  salvo que la empresa de que se trate tenga el equipo adecuado.</p>
<p>b.- las mercancías cuyo transporte haya sido prohibido por disposiciones legales o reglamentarias,  cuando tales disposiciones no prohíban precisamente el transporte de determinadas mercancías,  pero si ordenen la presentación de ciertos documentos para que puedan ser transportadas,  el “remitente” estará obligado a entregar al “porteador” los documentos correspondientes.</p>
<p>DECIMO CUARTA: Los casos no previstos en las presentes condiciones y las quejas derivadas de su aplicación se someterán por la vía administrativa a la secretaria de comunicaciones y transportes.</p>';
        $this->writeHTML($tbl, true, false, false, false, '');
        $this->lastPage();
    }

}
