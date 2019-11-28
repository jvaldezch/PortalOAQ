<?php
error_reporting(0);
/**
 * Description of Pedimentos
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Pedimentos {
    
    protected $_error;
    
    function __construct() {
        ;
    }
    
    /**
     * Este servicio permite obtener una lista de pedimentos tanto de importación como de exportación.
     * 
     * @param String $rfc Registro federal del contribuyente
     * @param String $token Token de seguridad
     * @param String $fecha_ini Fecha de inicio del periodo en formato yyyy-mm-dd
     * @param String $fecha_fin Fecha de fin del periodo en formato yyyy-mm-dd
     * @param int $aduana Aduana por donde se requiere las operaciones
     * @param String $tipo Tipo de operación que se requiere I o E
     * @return array
     */
    public function consultaPeriodo($rfc = null, $token = null, $fecha_ini = null, $fecha_fin = null, $aduana = null, $tipo = null)
    {
        $regex = "/^\d{4}\-\d{2}\-\d{2}$/";
        $r = filter_var($rfc, FILTER_SANITIZE_STRING);
        $t = filter_var($token, FILTER_SANITIZE_STRING);
        $fi = filter_var($fecha_ini,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>$regex)));
        $ff = filter_var($fecha_fin,FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>$regex)));        
        $a = filter_var($aduana, FILTER_SANITIZE_NUMBER_INT);
        $tp = filter_var($tipo, FILTER_SANITIZE_STRING);
        
        if($this->validateRfc($r)) {
            if($this->validateToken($t,$r)) {                
                if($this->validateAduana($a)) {
                    if($this->validateDate($fi, 'fecha_ini') && $this->validateDate($ff, 'fecha_fin')) {
                        if($this->validateDatesPeriod($fi, $ff)) {                            
                            if($this->validateOperation($tp)) {
                                $anexo24 = new OAQ_Anexo24($aduana);
                                $pedimentos = $anexo24->getDataByPeriod($r, $fi, $ff, $tp);
                                return $pedimentos;
                            } 
                        }                         
                    }
                }
            }            
        }
        return array('Error' => utf8_encode($this->_error));
        
    }
    
    protected function validateRfc($rfc)
    {
        if(!isset($rfc) || $rfc == '?' || $rfc == '') {
            $this->_error = 'No se especifico el RFC.';
            return false;
        }
        if(strlen($rfc) < 11) {
            $this->_error = 'Longitud del RFC no es válido.';
            return false;
        }
        return true;
    }
    
    protected function validateToken($token, $rfc = null)
    {
        if(!isset($token) || $token == '?' || $token == '') {
            $this->_error = 'No se especifico el token.';
            return false;
        }
        if(isset($rfc)) {
            if($token != sha1('dss78454'.$rfc.'oaq2013*')) {
                $this->_error = 'Token no válido para consulta.';
                return false;
            }
        }
        return true;
    }
    
    protected function validateAduana($aduana)
    {
        if(!isset($aduana) || $aduana == '?' || $aduana == '') {
            $this->_error = 'Aduana no especificada.';
            return false;
        }        
        $anexo24 = new OAQ_Anexo24($aduana);
        if(!$anexo24->valid) {
            $this->_error = 'Aduana por el momento no se puede consultar.';
            return false;
        }        
        return true;
    }
    
    protected function validateDate($fecha, $name)
    {
        if(!isset($fecha) || $fecha == '?' || $fecha == '') {
            $this->_error = "Valor de la fecha {$name} no especificado.";
            return false;
        }
        return true;
    }
    
    protected function validateDatesPeriod($fechaIni,$fechaFin)
    {
        if(!$this->isValidMysqlDate($fechaFin) && !$this->isValidMysqlDate($fechaIni)) {
            $this->_error = "Las fechas parecen no válidas.";
            return false;
        }
        if(strtotime($fechaIni) > strtotime($fechaFin)) {
            $this->_error = "La fecha de inicio del reporte no puede ser mayor que la fecha fin.";
            return false;
        }
        $datediff = strtotime($fechaFin) - strtotime($fechaIni);
        if(floor($datediff/(60*60*24)) > 31) {
            $this->_error = "El periodo de consulta no puede ser mayor a 31 días.";
            return false;
        }
        if(preg_match('/2012/', $fechaFin) || preg_match('/2012/', $fechaIni)) {
            $this->_error = "El periodo de consulta solo debe ser del año 2013 en adelante.";
            return false;
        }
        return true;
    }
    
    protected function validateOperation($tipo)
    {
        if(!isset($tipo) || $tipo == '?' || $tipo == '') {
            $this->_error = "Tipo de operación no especificado, dede ser I o E para importación o exportación respectivamente.";
            return false;
        }
        if(strlen($tipo) != 1) {
            $this->_error = "El tipo de operación no es válida.";
            return false;
        }
        if(!preg_match('/E|I/i', $tipo)) {
            $this->_error = "El tipo de operación no es válida.";
            return false;
        }
        return true;
    }
    
    protected function isValidMysqlDate($value)
    { 
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $value, $matches)) { 
            if (checkdate($matches[2], $matches[3], $matches[1])) { 
                return true; 
            } 
        }
        return false;
    }
    
}
