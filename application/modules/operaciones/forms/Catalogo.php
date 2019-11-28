<?php

class Operaciones_Form_Catalogo extends Twitter_Bootstrap_Form_Horizontal {

//    protected $_factura;
//
//    public function setFactura($factura = null) {
//        $this->_factura = $factura;
//    }

    public function init() {
        $this->addElement('select', 'clientes', array(
            'decorators'=> array ('ViewHelper','Errors', 'Label',), // no decorators
            'multiOptions' => array(
                'CTM990607US8' => 'APEX TOOLS GROUP',
            ),
            'attribs' => array('style' => 'width: 250px','tabindex' => '1'),
        ));
        $this->addElement('select', 'tipo', array(
            'decorators'=> array ('ViewHelper','Errors', 'Label',), // no decorators
            'multiOptions' => array(
                'imp_def' => 'Importaciones Definitivas',
                'imp_tmp' => 'Importaciones Temporales',
            ),
            'attribs' => array('style' => 'width: 250px','tabindex' => '1'),
        ));
//        $this->addElement('text', 'firmante', array(
//            'class' => 'inputop',
//            'attribs' => array('readonly' => 'true', 'style' => 'width: 150px'),
//        ));
//        $this->addElement('text', 'TipoOperacion', array(
//            'class' => 'inputop',
//            'attribs' => array('readonly' => 'true'),
//        ));
//        $this->addElement('text', 'Patente', array(
//            'class' => 'inputpatente',
//            'attribs' => array('readonly' => 'true'),
//        ));
//        $this->addElement('text', 'FactFacAju', array(
//            'class' => 'inputfacaju',
//            'attribs' => array('style' => 'width: 70px','tabindex' => '4'),            
//        ));
//        $this->addElement('text', 'Aduana', array(
//            'class' => 'inputaduana',
//            'attribs' => array('readonly' => 'true'),
//        ));
//        $this->addElement('text', 'Pedimento', array(
//            'class' => 'inputpedimento',
//            'attribs' => array('tabindex' => '1'),
//        ));
//        $this->addElement('text', 'Referencia', array(
//            'class' => 'inputreferencia',
//            'attribs' => array('tabindex' => '2'),
//        ));
//        $this->addElement('text', 'NumFactura', array(
//            'class' => 'inputnumfactura',
//            'attribs' => array('tabindex' => '3'),
//        ));
//        $this->addElement('select', 'Subdivision', array(
//            'class' => 'selectsubdiv',
//            'multiOptions' => array(
//                '0' => 'No',
//                '1' => 'Si',
//            ),
//            'attribs' => array('tabindex' => '6'),
//        ));
//        $this->addElement('select', 'CertificadoOrigen', array(
//            'class' => 'selectcert',
//            'multiOptions' => array(
//                '0' => 'No funge como certificado de origen',
//                '1' => 'Si funge como certificado de origen',
//            ),
//            'attribs' => array('style' => 'width: 250px','tabindex' => '7'),
//        ));
//        $this->addElement('text', 'NumExportador', array(
//            'class' => 'inputnumexp',
//            'attribs' => array('tabindex' => '8'),
//        ));
//
//        $this->addElement('text', 'FechaFactura', array(
//            'class' => 'inputfechafact',
//            'attribs' => array('tabindex' => '5'),
//        ));
//
//        $this->addElement('textarea', 'Observaciones', array(
//            'class' => 'focused',
//            'class' => 'span6',
//            'attribs' => array('style' => 'margin-bottom:0; width: 450px; height: 80px','tabindex' => '33'),
//        ));
//
//        $identif = array();
//        $identif[""] = '-- Seleccionar --';
//        $iden = new Vucem_Model_VucemIdentificadoresMapper();
//        $idens = $iden->getAll();
//        foreach ($idens as $item) {
//            $identif[$item["identificador"]] = $item["identificador"] . '_' . $item["descripcion"];
//        }
//
//        $paises = array();
//        $paises[""] = '-- Seleccionar --';
//        $country = new Vucem_Model_VucemPaisesMapper();
//        $countries = $country->getAllCountries();
//        foreach ($countries as $item) {
//            $paises[$item["cve_pais"]] = $item["nombre"];
//        }
//
//        /*         * **************************** DESTINATARIO ****************************************** */
//        $this->addElement('text', 'CteRfc', array(
//            'class' => 'inputcteid ctesearch',
//            'attribs' => array('autocomplete' => 'off','tabindex' => '21'),
//        ));
//        $this->addElement('select', 'CteIden', array(
//            'multiOptions' => $identif,
//            'class' => 'selectcteiden',
//            'attribs' => array('tabindex' => '22'),
//        ));
//        $this->addElement('hidden', 'CveCli', array());        
//        $this->addElement('text', 'CteNombre', array(
//            'class' => 'inputctenom',
//            'attribs' => array('tabindex' => '23'),
//        ));
//        $this->addElement('text', 'CteCalle', array(
//            'class' => 'inputcteca',
//            'attribs' => array('tabindex' => '24'),
//        ));
//        $this->addElement('text', 'CteNumExt', array(
//            'class' => 'inputctene',
//            'attribs' => array('tabindex' => '25'),
//        ));
//        $this->addElement('text', 'CteNumInt', array(
//            'class' => 'inputcteni',
//            'attribs' => array('tabindex' => '26'),
//        ));
//        $this->addElement('text', 'CteColonia', array(
//            'class' => 'inputcteco',
//            'attribs' => array('tabindex' => '27'),
//        ));
//        $this->addElement('text', 'CteLocalidad', array(
//            'class' => 'inputctelo',
//            'attribs' => array('tabindex' => '28'),
//        ));        
//        $this->addElement('text', 'CteMun', array(
//            'class' => 'inputctemun',
//            'attribs' => array('tabindex' => '29'),
//        ));
//        $this->addElement('text', 'CteEdo', array(
//            'class' => 'inputcteedo',
//            'attribs' => array('tabindex' => '30'),
//        ));
//        $this->addElement('text', 'CteCP', array(
//            'class' => 'inputctecp',
//            'attribs' => array('tabindex' => '31'),
//        ));
//        $this->addElement('select', 'CtePais', array(
//            'class' => 'selectctepais',
//            'multiOptions' => $paises,
//            'attribs' => array('tabindex' => '32'),
//        ));
//        /*         * **************************** EMISOR ****************************************** */
//        $this->addElement('text', 'ProTaxID', array(
//            'class' => 'inputprovid prosearch',
//            'attribs' => array('tabindex' => '9'),
//        ));
//        $this->addElement('select', 'ProIden', array(
//            'class' => 'selectproviden',
//            'multiOptions' => $identif,
//            'attribs' => array('tabindex' => '10'),
//        ));
//        $this->addElement('hidden', 'CvePro', array());        
//        $this->addElement('text', 'ProNombre', array(
//            'class' => 'inputprovnom',
//            'attribs' => array('tabindex' => '11'),
//        ));
//        $this->addElement('text', 'ProCalle', array(
//            'class' => 'inputprovca',
//            'attribs' => array('tabindex' => '12'),
//        ));
//        $this->addElement('text', 'ProNumExt', array(
//            'class' => 'inputprovni',
//            'attribs' => array('tabindex' => '13'),
//        ));
//        $this->addElement('text', 'ProNumInt', array(
//            'class' => 'inputprovne',
//            'attribs' => array('tabindex' => '14'),
//        ));
//        $this->addElement('text', 'ProColonia', array(
//            'class' => 'inputprovco',
//            'attribs' => array('tabindex' => '15'),
//        ));
//        $this->addElement('text', 'ProLocalidad', array(
//            'class' => 'inputprovlo',
//            'attribs' => array('tabindex' => '16'),
//        ));
//        $this->addElement('text', 'ProMun', array(
//            'class' => 'inputprovmun',
//            'attribs' => array('tabindex' => '17'),
//        ));
//        $this->addElement('text', 'ProEdo', array(
//            'class' => 'inputprovedo',
//            'attribs' => array('tabindex' => '18'),
//        ));
//        $this->addElement('text', 'ProCP', array(
//            'class' => 'inputprovcp',
//            'attribs' => array('tabindex' => '19'),
//        ));
//        $this->addElement('select', 'ProPais', array(
//            'class' => 'inputprovpais',
//            'multiOptions' => $paises,
//            'attribs' => array('tabindex' => '20'),
//        ));
    }

}
