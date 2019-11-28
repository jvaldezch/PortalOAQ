<?php

class Vucem_Form_NuevaFactura extends Twitter_Bootstrap_Form_Horizontal {

    protected $_factura;

    public function setFactura($factura = null) {
        $this->_factura = $factura;
    }

    public function init() {
        $decorators = array("ViewHelper", "Errors", "Label");

        $this->addElement("hidden", "IdFact", array(
            "decorators" => $decorators,
        ));

        $this->addElement("text", "firmante", array(
            "class" => "traffic-input-small",
            "attribs" => array("readonly" => "true"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "TipoOperacion", array(
            "class" => "traffic-input-small",
            "attribs" => array("readonly" => "true"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "Patente", array(
            "class" => "traffic-input-xs",
            "attribs" => array("readonly" => "true"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "FactFacAju", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "4"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "Aduana", array(
            "class" => "traffic-input-xs",
            "attribs" => array("readonly" => "true"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "Pedimento", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "1"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "Referencia", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "2"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "NumFactura", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "3", "style" => "width: 150px"),
            "decorators" => $decorators
        ));

        $this->addElement("select", "Subdivision", array(
            "class" => "traffic-select-small",
            "multiOptions" => array(
                "0" => "No",
                "1" => "Si",
            ),
            "attribs" => array("tabindex" => "6"),
            "decorators" => $decorators
        ));

        $this->addElement("select", "CertificadoOrigen", array(
            "class" => "traffic-select-medium",
            "multiOptions" => array(
                "0" => "No funge como certificado de origen",
                "1" => "Si funge como certificado de origen",
            ),
            "attribs" => array("tabindex" => "7"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "NumExportador", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "8"),
            "decorators" => $decorators
        ));

        $this->addElement("text", "FechaFactura", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "5"),
            "decorators" => $decorators
        ));

        $this->addElement("textarea", "Observaciones", array(
            "class" => "traffic-textarea-medium",
            "attribs" => array("tabindex" => "33"),
            "decorators" => $decorators
        ));

        $identif = array();
        $identif[""] = "-- Seleccionar --";
        $iden = new Vucem_Model_VucemIdentificadoresMapper();
        $idens = $iden->getAll();
        foreach ($idens as $item) {
            $identif[$item["identificador"]] = $item["identificador"] . "_" . $item["descripcion"];
        }

        $paises = array();
        $paises[""] = "-- Seleccionar --";
        $country = new Vucem_Model_VucemPaisesMapper();
        $countries = $country->getAllCountries();
        foreach ($countries as $item) {
            $paises[$item["cve_pais"]] = $item["nombre"];
        }

        /*         * **************************** DESTINATARIO ****************************************** */
        $this->addElement("text", "CteRfc", array(
            "class" => "traffic-input-small ctesearch uppercase",
            "attribs" => array("autocomplete" => "off", "tabindex" => "21"),
        ));

        $this->addElement("select", "CteIden", array(
            "multiOptions" => $identif,
            "class" => "traffic-select-small",
            "attribs" => array("tabindex" => "22"),
        ));

        $this->addElement("hidden", "CveCli", array());
        
        $this->addElement("text", "CteNombre", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "23"),
        ));

        $this->addElement("text", "CteCalle", array(
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "24"),
        ));

        $this->addElement("text", "CteNumExt", array(
            "class" => "traffic-input-xs",
            "attribs" => array("tabindex" => "25"),
        ));

        $this->addElement("text", "CteNumInt", array(
            "class" => "traffic-input-xs",
            "attribs" => array("tabindex" => "26"),
        ));

        $this->addElement("text", "CteColonia", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "27"),
        ));

        $this->addElement("text", "CteLocalidad", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "28"),
        ));

        $this->addElement("text", "CteMun", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "29"),
        ));

        $this->addElement("text", "CteEdo", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "30"),
        ));

        $this->addElement("text", "CteCP", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "31"),
        ));

        $this->addElement("select", "CtePais", array(
            "class" => "traffic-select-large",
            "multiOptions" => $paises,
            "attribs" => array("tabindex" => "32"),
        ));

        /*         * **************************** EMISOR ****************************************** */
        $this->addElement("text", "ProTaxID", array(
            "class" => "traffic-input-small prosearch uppercase",
            "attribs" => array("tabindex" => "9"),
        ));

        $this->addElement("select", "ProIden", array(
            "class" => "traffic-select-small",
            "multiOptions" => $identif,
            "attribs" => array("tabindex" => "10"),
        ));

        $this->addElement("hidden", "CvePro", array());

        $this->addElement("text", "ProNombre", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "11", "style" => "width: 350px;"),
        ));

        $this->addElement("text", "ProCalle", array(
            "class" => "traffic-input-large",
            "attribs" => array("tabindex" => "12"),
        ));

        $this->addElement("text", "ProNumExt", array(
            "class" => "traffic-input-xs",
            "attribs" => array("tabindex" => "13"),
        ));

        $this->addElement("text", "ProNumInt", array(
            "class" => "traffic-input-xs",
            "attribs" => array("tabindex" => "14"),
        ));

        $this->addElement("text", "ProColonia", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "15"),
        ));

        $this->addElement("text", "ProLocalidad", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "16"),
        ));

        $this->addElement("text", "ProMun", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "17"),
        ));

        $this->addElement("text", "ProEdo", array(
            "class" => "traffic-input-medium",
            "attribs" => array("tabindex" => "18"),
        ));

        $this->addElement("text", "ProCP", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "19"),
        ));

        $this->addElement("select", "ProPais", array(
            "class" => "traffic-select-large",
            "multiOptions" => $paises,
            "attribs" => array("tabindex" => "20"),
        ));
    }

}
