<?php

class Vucem_Form_AddNewProduct extends Twitter_Bootstrap_Form_Horizontal {

    protected $_factura;
    protected $_idprod;

    public function setFactura($factura = null) {
        $this->_factura = $factura;
    }

    public function setIdprod($idprod = null) {
        $this->_idprod = $idprod;
    }

    public function init() {
        $this->setIsArray(true);
        $decorators = array("ViewHelper", "Errors", "Label");

        $vucemMon = new Vucem_Model_VucemMonedasMapper();
        $monedas = $vucemMon->getAllCurrencies();
        $dataMon[""] = "--";
        foreach ($monedas as $mon) {
            $dataMon[$mon["codigo"]] = $mon["codigo"];
        }

        $vucemUmc = new Vucem_Model_VucemUmcMapper();
        $umcs = $vucemUmc->getAllUnits();
        $dataUmc[""] = "--";
        foreach ($umcs as $umc) {
            $dataUmc[$umc["clave"]] = $umc["clave"] . " - " . $umc["desc"];
        }

        $countries = new Vucem_Model_VucemPaisesMapper();
        $paises = $countries->getAllCve();
        $dataPa[""] = "--";
        foreach ($paises as $pais) {
            $dataPa[$pais["cve_pais"]] = $pais["cve_pais"];
        }

        $vucemUni = new Vucem_Model_VucemUnidadesMapper();
        $unidades = $vucemUni->getAllUnits();
        $dataUn[""] = "--";
        foreach ($unidades as $un) {
            $dataUn[$un["unidad_medida"]] = $un["unidad_medida"] . " - " . $un["desc_es"];
        }

        $this->addElement("hidden", "ID_FACT", array());

        $this->addElement("hidden", "ID_PROD", array());

        $this->addElement("text", "ORDEN", array(
            "class" => "traffic-input-tiny",
            "attribs" => array("tabindex" => "34"),
            "decorators" => $decorators,
        ));
        $this->addElement("text", "CODIGO", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "35"),
            "decorators" => $decorators,
        ));
        $this->addElement("text", "PARTE", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "36"),
            "decorators" => $decorators,
        ));
        $this->addElement("textarea", "DESC_COVE", array(
            "class" => "traffic-textarea-small",
            "attribs" => array("tabindex" => "37"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "CANTFAC", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "38"),
            "decorators" => $decorators,
        ));
        $this->addElement("text", "PREUNI", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "39"),
            "decorators" => $decorators,
        ));
        $this->addElement("select", "MONVAL", array(
            "multiOptions" => $dataMon,
            "class" => "traffic-select-xs",
            "attribs" => array("tabindex" => "40"),
            "decorators" => $decorators,
        ));
        $this->addElement("text", "VALCOM", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "41"),
            "decorators" => $decorators,
        ));
        $this->addElement("text", "VALCEQ", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "42"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "VALDLS", array(
            "class" => "traffic-input-small",
            "attribs" => array("tabindex" => "43"),
            "decorators" => $decorators,
        ));

        $this->addElement("select", "UMC", array(
            "multiOptions" => $dataUmc,
            "class" => "traffic-select-xs",
            "attribs" => array("tabindex" => "44"),
            "decorators" => $decorators,
        ));

        $this->addElement("select", "UMC_OMA", array(
            "multiOptions" => $dataUn,
            "class" => "traffic-select-xs",
            "attribs" => array("tabindex" => "45"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "MARCA", array(
            "class" => "traffic-select-small",
            "attribs" => array("tabindex" => "46"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "MODELO", array(
            "class" => "traffic-select-small",
            "attribs" => array("tabindex" => "47"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "SUBMODELO", array(
            "class" => "traffic-select-small",
            "attribs" => array("tabindex" => "48"),
            "decorators" => $decorators,
        ));

        $this->addElement("text", "NUMSERIE", array(
            "class" => "traffic-select-small",
            "attribs" => array("tabindex" => "49"),
            "decorators" => $decorators,
        ));
    }

}
