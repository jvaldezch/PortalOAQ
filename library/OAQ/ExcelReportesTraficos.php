<?php

require_once "Spout/Autoloader/autoload.php";

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;

/**
 * Description of Reportes
 *
 * @author Jaime
 */
class ExcelReportesTraficos {

    protected $initRow;
    protected $excel;
    protected $range;
    protected $title;
    protected $titles;
    protected $data;
    protected $filename;
    protected $appConfig;
    protected $style = array(
        "font" => array(
            "bold" => true,
            "name" => "Arial",
            "size" => 10,
            "color" => array("rgb" => "FFFFFF")
        ),
        "alignment" => array(
            "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        "borders" => array(
            "allborders" => array(
                "style" => PHPExcel_Style_Border::BORDER_MEDIUM,
                "color" => array("rgb" => "222222"),
            )
        ),
        "fill" => array(
            "type" => PHPExcel_Style_Fill::FILL_SOLID,
            "color" => array("rgb" => "538dd5")
        )
    );
    protected $styleh = array(
        "fill" => array(
            "type" => PHPExcel_Style_Fill::FILL_SOLID,
            "color" => array("rgb" => "c5d9f1")
        )
    );
    protected $stylec = array(
        "font" => array(
            "name" => "Arial",
            "size" => 10,
            "color" => array("rgb" => "222222")
        ),
        "alignment" => array(
            "horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
    );

    function getStyle() {
        return $this->style;
    }

    function getStyleh() {
        return $this->styleh;
    }

    function getStylec() {
        return $this->stylec;
    }

    function setFilename($filename) {
        $this->filename = $filename;
    }

    function setData($data) {
        $this->data = $data;
    }

    function setTitles($titles) {
        $this->titles = $titles;
    }

    function __construct() {
        $this->excel = new PHPExcel();
        $this->appConfig = new Application_Model_ConfigMapper();
    }

    protected function _autosize($i) {
        $this->excel->getActiveSheet()->getStyle($this->range[0] . ($this->initRow + 1) . ":{$this->range[1]}{$i}")->applyFromArray($this->stylec);
        foreach (range($this->range[0], $this->range[1]) as $columnID) {
            $this->excel->getActiveSheet()
                    ->getColumnDimension($columnID)
                    ->setAutoSize(true);
        }
    }

    protected function _ini($sheet = null) {
        if ($sheet !== null) {
            $this->excel->createSheet();
            $this->excel->setActiveSheetIndex($sheet);
        } else {
            $this->excel->setActiveSheetIndex(0);
        }
        $this->excel->getActiveSheet()->setTitle($this->title);
        $this->excel->getActiveSheet()->fromArray($this->titles, null, "{$this->range[0]}{$this->initRow}");
        $this->excel->getActiveSheet()->getStyle("{$this->range[0]}{$this->initRow}:{$this->range[1]}{$this->initRow}")->applyFromArray($this->style);
    }

}
