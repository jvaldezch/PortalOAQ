<?php

class Operaciones_Model_ValidationsMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get('oaqintranet');
    }

    public function getValidationFile($id, $table) {
        try {
            $sql = $this->_db->select()
                    ->from($table, array('archivo_content'))
                    ->where('id = ?', $id);

            $stmt = $this->_db->fetchRow($sql, array());

            return $stmt;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getValidationFilename($id, $table) {
        try {
            $sql = $this->_db->select()
                    ->from($table, array('archivo_nombre'))
                    ->where('id = ?', $id);

            $stmt = $this->_db->fetchRow($sql, array());

            return $stmt['archivo_nombre'];
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Checar si el archivo M3 existe.
     * 
     * 
     * 
     */
    public function checkForValidationFile($filename, $patente, $pedimento, $aduana) {
        try {
            $sql = $this->_db->select()
                    ->from('archivos_m3')
                    ->where('archivo_nombre = ?', $filename)
                    ->where('patente = ?', $patente)
                    ->where('pedimento = ?', $pedimento)
                    ->where('aduana = ?', $aduana);

            $stmt = $this->_db->fetchRow($sql, array());

            if ($stmt) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function addNewM3File($archivoNom, $archivoNum, $archivoNumJuliano, $patente, $pedimento, $aduana, $rfc, $cveDoc, $content, $fEntrada, $fPago, $fExtract, $fPresentacion, $fImp, $fOriginal, $tipoMov) {
        try {
            $data = array(
                'archivo_nombre' => $archivoNom,
                'archivo_num' => $archivoNum,
                'archivo_num_juliano' => $archivoNumJuliano,
                'patente' => $patente,
                'pedimento' => $pedimento,
                'aduana' => $aduana,
                'rfc' => $rfc,
                'cve_doc' => $cveDoc,
                'archivo_content' => $content,
                'creado' => date('Y-m-d H:i:s', time()),
                'fecha_entrada' => $fEntrada,
                'fecha_pago' => $fPago,
                'fecha_extraccion' => $fExtract,
                'fecha_presentacion' => $fPresentacion,
                'fecha_impeuacan' => $fImp,
                'fecha_original' => $fOriginal,
                'tipo_mov' => $tipoMov,
            );

            $insert = $this->_db->insert('archivos_m3', $data);

            if ($insert) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            if (isset($data)) {
                Zend_Debug::dump($data);
            }
            echo '<b>Db error adding new "archivo validación M3":</b>' . $e->getMessage();
            die();
        }
    }

    public function getClientId($rfc) {
        try {
            $sql = $this->_db->select()
                    ->from('clientes', array('id'))
                    ->where('rfc = ?', $rfc);

            $stmt = $this->_db->fetchRow($sql, array());

            if ($stmt) {
                return $stmt['id'];
            } else {
                return 0;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    public function checkForPreValidationFile($archivoNom) {
        try {
            $sql = $this->_db->select()
                    ->from('archivos_prevalidacion')
                    ->where('archivo_nombre = ?', $archivoNom);

            $stmt = $this->_db->fetchRow($sql, array());

            if ($stmt) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function addNewPrevalidationFile($archivoNom, $archivoNum, $archivoNumJuliano, $fecha, $content) {
        try {
            $data = array(
                'archivo_nombre' => $archivoNom,
                'archivo_num' => $archivoNum,
                'archivo_num_juliano' => $archivoNumJuliano,
                'fecha' => $fecha,
                'archivo_content' => $content,
                'creado' => date('Y-m-d H:i:s', time()),
            );

            $insert = $this->_db->insert('archivos_prevalidacion', $data);

            if ($insert) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            if (isset($data)) {
                Zend_Debug::dump($data);
            }
            echo '<b>Db error adding new "archivo prevalidación":</b>' . $e->getMessage();
            die();
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    public function checkForResponseFile($archivoNom) {
        try {
            $sql = $this->_db->select()
                    ->from('archivos_respuesta')
                    ->where('archivo_nombre = ?', $archivoNom);

            $stmt = $this->_db->fetchRow($sql, array());

            if ($stmt) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function addNewResponseFile($archivoNom, $archivoNum, $archivoNumJuliano, $pedimento, $firma, $error, $content) {
        try {
            $data = array(
                'archivo_nombre' => $archivoNom,
                'archivo_num' => $archivoNum,
                'archivo_num_juliano' => $archivoNumJuliano,
                'pedimento' => $pedimento,
                'archivo_content' => $content,
                'firma' => $firma,
                'error' => $error,
                'creado' => date('Y-m-d H:i:s', time()),
            );

            $insert = $this->_db->insert('archivos_respuesta', $data);

            if ($insert) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            if (isset($data)) {
                Zend_Debug::dump($data);
            }
            echo '<b>Db error adding new "archivo respuesta":</b>' . $e->getMessage();
            die();
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    public function checkForResponsePayFile($archivoNom) {
        try {
            $sql = $this->_db->select()
                    ->from('archivos_respuestapago')
                    ->where('archivo_nombre = ?', $archivoNom);

            $stmt = $this->_db->fetchRow($sql, array());

            if ($stmt)
                return true;
            else
                return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function addNewResponsePayFile($archivoNom, $archivoNum, $archivoNumJuliano, $pedimento, $rfc, $fecha, $content) {
        try {

            $data = array(
                'archivo_nombre' => $archivoNom,
                'archivo_num' => $archivoNum,
                'archivo_num_juliano' => $archivoNumJuliano,
                'archivo_content' => $content,
                'pedimento' => $pedimento,
                'rfc' => $rfc,
                'fecha' => $fecha,
                'creado' => date('Y-m-d H:i:s', time()),
            );

            $insert = $this->_db->insert('archivos_respuestapago', $data);

            if ($insert) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            if (isset($data)) {
                Zend_Debug::dump($data);
            }
            echo '<b>Db error adding new "respuesta de pago":</b>' . $e->getMessage();
            die();
        }
    }

    /**
     * 
     * 
     * 
     * 
     */
    public function getAllM3DataNull($columnName) {
        $sql = $this->_db->select()
                ->from('archivos_m3', array('id', 'archivo_num_juliano', 'archivo_num'))
                ->where($columnName . ' IS NULL');
        $stmt = $this->_db->fetchAll($sql, array());
        if ($stmt)
            return $stmt;
        else
            return NULL;
    }

    public function updateM3ColumnId($column, $id, $fileId) {
        $data = array(
            $column => $fileId,
        );
        $where['id = ?'] = $id;
        $this->_db->update('archivos_m3', $data, $where);
    }

    public function getFileIsIfExists($table, $column, $archivo) {
        $sql = $this->_db->select()
                ->from($table, array('id'))
                ->where($column . ' = ?', $archivo);
        $stmt = $this->_db->fetchRow($sql, array());
        if ($stmt)
            return $stmt;
        else
            return NULL;
    }

    public function updatePayM3($m3Id, $pedimento, $envioId, $pagoId) {
        $data = array(
            'envioid' => $envioId,
            'respuestaid' => $pagoId,
            'pedimento' => $pedimento,
        );

        $where['id = ?'] = $m3Id;
        $this->_db->update('archivos_m3', $data, $where);
    }

    /**
     * 
     * 
     * 
     * 
     */
    public function searchM3Files($rfc) {
        //$clienteId = $this->getClientId($rfc);

        $sql = $this->_db->select()
                ->from('archivos_m3', array('id', 'archivo_nombre', 'patente', 'aduana', 'pedimento', 'tipo_mov', 'cve_doc', 'prevalidacionid', 'validacionid', 'envioid', 'respuestaid', 'fecha_pago', 'coves'))
                ->where('rfc LIKE ?', $rfc)
                ->order('pedimento ASC');

        $stmt = $this->_db->fetchAll($sql, array());
        if ($stmt)
            return $stmt;
        else
            return NULL;
    }

    public function getM3Data($id) {
        $sql = $this->_db->select()
                ->from('archivos_m3')
                ->where('id = ?', $id);

        $stmt = $this->_db->fetchRow($sql, array());

        if ($stmt)
            return $stmt;
        else
            return NULL;
    }

    /**
     * 
     * 
     * 
     * 
     */
    public function searchCoveFiles($rfc) {
        $sql = $this->_db->select()
                ->from('archivos_coves', array('id', 'archivo_nombre', 'num_factura', 'patente', 'emisor_rfc', 'destinatario_rfc', 'tipo_moneda', 'cantidad', 'valor_unitario', 'valor_total', 'respcoveid', 'fecha_exp'))
                ->where('emisor_rfc LIKE ?', $rfc)
                ->order('fecha_exp ASC');

        $stmt = $this->_db->fetchAll($sql, array());
        if ($stmt)
            return $stmt;
        else
            return NULL;
    }

    public function getFilename($id, $table) {
        $sql = $this->_db->select()
                ->from($table, array('archivo_nombre'))
                ->where('id = ?', $id);

        $stmt = $this->_db->fetchRow($sql, array());

        if ($stmt)
            return $stmt['archivo_nombre'];
        else
            return NULL;
    }

    public function getResponseSignature($id) {
        $sql = $this->_db->select()
                ->from('archivos_respuesta', array('firma'))
                ->where('id = ?', $id);

        $stmt = $this->_db->fetchRow($sql, array());

        if ($stmt)
            return $stmt['firma'];
        else
            return NULL;
    }

    /**
     * 
     */
    public function getPayDate($id) {
        $sql = $this->_db->select()
                ->from('archivos_respuestapago', array('fecha'))
                ->where('id = ?', $id);

        $stmt = $this->_db->fetchRow($sql, array());

        if ($stmt)
            return date('Y/m/d', strtotime($stmt['fecha']));
        //return date('Y-m-d H:i:s', strtotime($stmt['fecha']));
        else
            return NULL;
    }

    public function checkForCove($archNom, $archNum, $patente) {
        $sql = $this->_db->select()
                ->from('archivos_coves', array('id'))
                ->where('archivo_nombre = ?', $archNom)
                ->where('archivo_num = ?', $archNum)
                ->where('patente = ?', $patente);

        $stmt = $this->_db->fetchRow($sql, array());

        if ($stmt)
            return true;
        else
            return NULL;
    }

    public function checkForCoveAux($archNom, $archNum, $archJuliano, $table) {
        $sql = $this->_db->select()
                ->from($table, array('id'))
                ->where('archivo_nombre = ?', $archNom)
                ->where('archivo_num = ?', $archNum)
                ->where('archivo_num_juliano = ?', $archJuliano);

        $stmt = $this->_db->fetchRow($sql, array());

        if ($stmt)
            return true;
        else
            return NULL;
    }

    public function addNewCoveFile($archNom, $archNum, $archJuliano, $archContent, $fExp, $tipoMov, $tipoFig, $email, $numFact, $patente, $consultaRfc, $emisorTipoIden, $emisorRfc, $emisorNom, $destTipoIden, $destRfc, $destNom, $tipoMoneda, $cant, $valorU, $valorT) {
        $data = array(
            'archivo_nombre' => $archNom,
            'archivo_num' => $archNum,
            'archivo_num_juliano' => $archJuliano,
            'archivo_content' => $archContent,
            'fecha_exp' => $fExp,
            'tipo_mov' => $tipoMov,
            'tipo_figura' => $tipoFig,
            'email' => $email,
            'num_factura' => $numFact,
            'patente' => $patente,
            'consulta_rfc' => $consultaRfc,
            'emisor_tipoiden' => $emisorTipoIden,
            'emisor_rfc' => $emisorRfc,
            'emisor_nombre' => $emisorNom,
            'destinatario_tipoiden' => $destTipoIden,
            'destinatario_rfc' => $destRfc,
            'destinatario_nombre' => $destNom,
            'tipo_moneda' => $tipoMoneda,
            'cantidad' => $cant,
            'valor_unitario' => $valorU,
            'valor_total' => $valorT,
            'creado' => date('Y-m-d H:i:s', time()),
        );

        $insert = $this->_db->insert('archivos_coves', $data);

        if ($insert) {
            return true;
        }
        return false;
    }

    public function updateCoveColumn($column, $id, $respid) {
        $data = array(
            $column => $respid,
        );
        $where['id = ?'] = $id;
        $this->_db->update('archivos_coves', $data, $where);
    }

    /**
     * Obtener el XML del COVE
     * 
     * @param int $id
     * @return String
     */
    public function getCoveXml($id) {
        if ($id != NULL) {
            $sql = $this->_db->select()
                    ->from('archivos_coves', array('archivo_content'))
                    ->where('id = ?', $id);

            $stmt = $this->_db->fetchRow($sql, array());

            if ($stmt)
                return $stmt['archivo_content'];
            else
                return NULL;
        } else {
            return NULL;
        }
    }

    /**
     * Regresa la relación de los COVES de cada pedimento.
     * 
     * @param String $fechaIni
     * @param String $fechaFin
     */
    public function covePedimento($rfc, $fechaIni, $fechaFin) {
        try {

            /* $sql = "SELECT 
              m3.pedimento,
              m3.aduana,
              m3.archivo_content,
              r.firma
              FROM archivos_m3 AS m3
              LEFT JOIN archivos_respuesta AS r ON r.pedimento = m3.pedimento
              WHERE m3.rfc LIKE '{$rfc}'
              AND m3.fecha_pago >= '{$fechaIni}'
              AND m3.fecha_pago <= '{$fechaFin}'
              AND r.firma IS NOT NULL
              AND r.error IS NULL
              GROUP BY pedimento, aduana, firma;"; */

            $sql = $this->_db->select();

            $sql->from(array('m3' => 'archivos_m3'), array('m3.pedimento', 'm3.aduana', 'm3.archivo_content'))
                    ->join(array('r' => 'archivos_respuesta'), 'r.pedimento = m3.pedimento', array('r.firma'))
                    ->where('m3.rfc = ?', $rfc)
                    ->where('m3.fecha_pago >= ?', $fechaIni)
                    ->where('m3.fecha_pago <= ?', $fechaFin)
                    ->where('r.firma IS NOT NULL')
                    ->where('r.error IS NULL')
                    ->group(array('pedimento', 'aduana', 'firma'));

            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        'pedimento' => $item['pedimento'],
                        'aduana' => $item['aduana'],
                        'archivo_content' => $item['archivo_content'],
                        'firma' => $item['firma'],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
