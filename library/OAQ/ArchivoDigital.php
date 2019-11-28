<?php

/**
 * Envio de emails que usa plantilla que se leen basadas en DOM parsing
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_ArchivoDigital
{
    protected $_config;
    protected $_tmpFolder;
    
    function __construct() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        
        if (!file_exists($this->_config->tmp->expedientes->folder)) {
            mkdir($this->_config->tmp->expedientes->folder);
        }
        $this->_tmpFolder = $this->_config->tmp->expedientes->folder;
    }
    
    public function createMultiPageTiff($cta_gastos,$aduana,$referencia,$content)
    {
        try {
            $filename = $this->_tmpFolder . DIRECTORY_SEPARATOR . $cta_gastos . '_' . $aduana . '_' . $referencia.'.tif'; 
            if(!file_exists($filename)) {
                file_put_contents($filename, $content);
                return $this->_tmpFolder;
            }
            return $this->_tmpFolder;
        } catch (Exception $e) {
            return NULL;
        }
    }   
    
}
