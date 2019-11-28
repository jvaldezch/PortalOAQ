<?php

require_once "Spout/Autoloader/autoload.php";

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OAQ_Trafico_Plantillas {
    
    protected $appConfig;
    protected $idTrafico;
    protected $traficos;
            
    function setIdTrafico($idTrafico) {
        $this->idTrafico = $idTrafico;
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property " . __METHOD__);
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
        if ($this->idTrafico) {
            $this->traficos = new OAQ_Trafico(array("idTrafico" => $this->idTrafico));
        }
        $this->appConfig = new Application_Model_ConfigMapper();
    }
    
    public function rawdata() {
        $arr = $this->traficos->obtenerFacturas();
        if (!empty($arr)) {
            var_dump($arr);
            foreach ($arr as $row) {
                $arrp = $this->traficos->obtenerProductosFactura($row["idFactura"]);
                var_dump($arrp);
                foreach ($arrp as $rowp) {
                }
            }
        }
    }
    
    public function plantillaCasa() {
        if (isset($this->traficos)) {            
            $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
            $tstyle = (new StyleBuilder())
                    ->setFontBold()
                    ->setFontSize(10)
                    ->setFontName("Arial")
                    ->setFontColor(Color::BLACK)
                    ->setBackgroundColor("c6d9f0")
                    ->setBorder($border)
                    ->build();
            $dstyle = (new StyleBuilder())
                    ->setFontSize(10)
                    ->setFontName("Arial")
                    ->setFontColor(Color::BLACK)
                    ->build();
            $writer = WriterFactory::create(Type::XLSX);
            if (APPLICATION_ENV == "production") {
                $writer->setTempFolder($this->appConfig->getParam("tmpDir"));            
            } else {
                $writer->setTempFolder("C:\\wamp64\\tmp");            
            }
            $writer->openToBrowser("PLANTILLA_CASA_" . time() . ".xlsx");
            
            $firstSheet = $writer->getCurrentSheet();
            
            $writer->addRowWithStyle(array(
                "CLAVE DE PROVEEDOR",
                "NUMERO DE FACTURA",
                "FECHA DE FACTURA",
                "VALOR EN MONEDA EXTRANJERA",
                "NONEDA DE LA FACTURA",
                "INCOTERM",
                "EXISTE SUBDIVISION",
                "CERTIFICADO DE ORIGEN",
                "NUMERO DE PARTE",
                "PAIS ORIGEN",
                "PAIS VENDEDOR",
                "FRACCION",
                "DESCRIPCION DE LA MERCANCIA",
                "PRECIO DE LA PARTIDA",
                "UNIDAD DE LA FACTURA/COVE",
                "CANTIDAD DE LA FACTURA/COVE",
                "CANTIDAD DE LA TARIFA",
                "PREFERENCIA ARANCELARIA",
                "MARCA",
                "SUBMODELO",
                "NUMERO DE SERIE"
            ), $tstyle);
            
            $arr = $this->traficos->obtenerFacturas();            
            if (!empty($arr)) {
                foreach ($arr as $row) {
                    $arrp = $this->traficos->obtenerProductosFactura($row["idFactura"]);
                    foreach ($arrp as $rowp) {
                        $writer->addRowWithStyle(array(
                            $row["cvePro"],
                            $row["numFactura"],
                            $row["fechaFactura"],
                            $row["valorFacturaMonExt"],
                            $row["divisa"],
                            $row["incoterm"],
                            $row["subdivision"],
                            $row["certificadoOrigen"],
                            $rowp["numParte"],
                            $rowp["paisOrigen"],
                            $rowp["paisVendedor"],
                            $rowp["fraccion"],
                            $rowp["descripcion"],
                            $rowp["valorComercial"],
                            $rowp["umc"],
                            $rowp["cantidadFactura"],
                            $rowp["cantidadTarifa"],
                            null,
                            $rowp["marca"],
                            $rowp["subModelo"],
                            $rowp["numSerie"],
                                ), $dstyle);
                    }
                }
            }
            $writer->close();
            
        } else {
            throw new Exception("Traficos is not set!");
        }
    }
    
    public function plantillaSlam() {
        if (isset($this->traficos)) {            
            $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();
            $tstyle = (new StyleBuilder())
                    ->setFontBold()
                    ->setFontSize(10)
                    ->setFontName("Arial")
                    ->setFontColor(Color::BLACK)
                    ->setBackgroundColor("c6d9f0")
                    ->setBorder($border)
                    ->build();
            $dstyle = (new StyleBuilder())
                    ->setFontSize(10)
                    ->setFontName("Arial")
                    ->setFontColor(Color::BLACK)
                    ->build();
            $writer = WriterFactory::create(Type::XLSX);
            if (APPLICATION_ENV == "production") {
                $writer->setTempFolder($this->appConfig->getParam("tmpDir"));            
            } else {
                $writer->setTempFolder("C:\\wamp64\\tmp");            
            }
            $writer->openToBrowser("PLANTILLA_SLAM_" . time() . ".xlsx");
            
            $firstSheet = $writer->getCurrentSheet();
            $firstSheet->setName("Facturas");
            
            $writer->addRowWithStyle(array(
                "NUMERO",
                "PROVEEDOR",
                "VALOR FACTURA",
                "FLETES",
                "SEGUROS",
                "EMBALAJES",
                "OTROS",
                "INCOTERM",
                "METODO VALORACION",
                "MONEDA",
                "PAIS FACTURACION",
                "FECHA",
            ), $tstyle);
            $arr = $this->traficos->obtenerFacturas();            
            if (!empty($arr)) {
                foreach ($arr as $row) {
                    $writer->addRowWithStyle(array(
                        $row["numFactura"],
                        $row["cvePro"],
                        $row["valorFacturaMonExt"],
                        null,
                        null,
                        null,
                        null,
                        $row["incoterm"],
                        null,
                        $row["divisa"],
                        $row["paisFactura"],
                        $row["fechaFactura"],
                            ), $dstyle);
                }
            }
            
            
            $secondSheet = $writer->addNewSheetAndMakeItCurrent();
            $secondSheet->setName("Datos");
            
            $writer->addRowWithStyle(array(
                "NUMERO FACTURA",
                "LN",
                "PARTE",
                "FRACCION",
                "DESC INGLES",
                "DESC ESPANOL",
                "PAIS",
                "TLC",
                "Unidad",
                "CANTIDAD",
                "PRECIO",
                "PESO(KGS)",
                "TOTAL",
            ), $tstyle);
            
            $writer->close();
            
        } else {
            throw new Exception("Traficos is not set!");
        }
    }

}
