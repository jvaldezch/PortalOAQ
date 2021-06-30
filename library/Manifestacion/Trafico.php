<?php

class Manifestacion_Trafico
{
    protected $_config;
    protected $_firephp;
    protected $_aduanas;
    protected $_claves;

    public function __construct(array $options = null)
    {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_firephp = Zend_Registry::get("firephp");

        $this->_aduanas = new Trafico_Model_TraficoAduanasMapper();
        $this->_claves = new Trafico_Model_TraficoCvePedMapper();
    }

    protected function _obtenerRegimen($tipoOperacion, $cvePedimento)
    {
        $regimen = null;
        if (null !== ($reg = $this->_claves->buscarRegimen($cvePedimento))) {
            if ($tipoOperacion == "TOCE.IMP") {
                $regimen = $reg["regimenImportacion"];
            } else {
                $regimen = $reg["regimenExportacion"];
            }
        }
        return $regimen;
    }

    public function todas($page = 1, $rows = 20)
    {
        $mppr = new Manifestacion_Model_Manifestaciones();
        $sql = $mppr->todas(true);
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($sql));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($rows);
        $resp = array(
            "total" => $paginator->getTotalItemCount(),
            "rows" => (array) $paginator->getCurrentItems(),
        );
        return $resp;
    }

    public function datos($id)
    {
        $mppr = new Manifestacion_Model_Manifestaciones();
        $row = $mppr->datos($id);        
        return $row;
    }

    public function datosTrafico($idAduana, $referencia)
    {
        $mppr = new Trafico_Model_TraficosMapper();
        $row = $mppr->busquedaReferencia($idAduana, $referencia);        
        return $row;
    }

    public function nueva($idAduana, $idCliente, $tipoOperacion, $cvePedimento, $pedimento, $referencia)
    {
        $mppr = new Manifestacion_Model_Manifestaciones();
        $id = $mppr->verificar($idAduana, $idCliente, $pedimento, $referencia);
        if (!$id) {
            $arr = $this->_aduanas->obtenerAduana($idAduana);
            $regimenAduanero = $this->_obtenerRegimen($tipoOperacion, $cvePedimento);
            $id = $mppr->nueva($idAduana, $idCliente, $arr["patente"], $arr["aduana"], $tipoOperacion, $cvePedimento, $pedimento, $referencia, $regimenAduanero);
            return $id;
        }
        return $id;
    }

    public function edocuments($id)
    {
        $mppr = new Manifestacion_Model_ManifestacionEdocuments();
        return $mppr->todos($id);
    }

    public function rfcConsulta($id)
    {
        $mppr = new Manifestacion_Model_ManifestacionRfcConsulta();
        return $mppr->todos($id);
    }

    public function edocumentsTrafico($patente, $aduana, $pedimento, $referencia)
    {
        $mppr = new Archivo_Model_RepositorioMapper();
        $rows = $mppr->edocuments($referencia, $patente, $aduana);
        if ($rows) {
            return $rows;
        }
        return;        
    }

    public function agregarEdocument($id, $edocument)
    {
        $mppr = new Manifestacion_Model_ManifestacionEdocuments();
        $v = $mppr->verificar($id, $edocument);
        if (!$v) {
            return $mppr->agregar($id, $edocument);
        }
        return;        
    }
}
