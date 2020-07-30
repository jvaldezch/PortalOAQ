<?php

class Operaciones_Model_Incidencias
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Operaciones_Model_DbTable_Incidencias();
    }

    public function incidenciasSelect()
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("i" => "incidencias"), array("*"))
                ->joinLeft(array("c" => "trafico_clientes"), "i.idCliente = c.id", array("nombre"))
                ->joinLeft(array("a" => "trafico_aduanas"), "i.idAduana = a.id", array("patente", "aduana"))
                ->joinLeft(array("e" => "incidencia_tipo_error"), "e.id = i.idTipoError", array("tipoError"));
            return $sql;
        } catch (Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtener($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($id, $arr)
    {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }


    public function verificar($idAduana, $idCliente, $pedimento, $referencia)
    {
        try {
            $sql = $this->_db_table->select()
                ->where("idAduana = ?", $idAduana)
                ->where("idCliente = ?", $idCliente)
                ->where("pedimento = ?", $pedimento)
                ->where("referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr)
    {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrar($id)
    {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function reporte($year, $idCliente = null, $idAduana = null)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 1 THEN 1 ELSE 0 END) AS Ene"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 2 THEN 1 ELSE 0 END) AS Feb"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 3 THEN 1 ELSE 0 END) AS Mar"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 4 THEN 1 ELSE 0 END) AS Abr"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 5 THEN 1 ELSE 0 END) AS May"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 6 THEN 1 ELSE 0 END) AS Jun"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 7 THEN 1 ELSE 0 END) AS Jul"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 8 THEN 1 ELSE 0 END) AS Ago"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 9 THEN 1 ELSE 0 END) AS Sep"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 10 THEN 1 ELSE 0 END) AS 'Oct'"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 11 THEN 1 ELSE 0 END) AS Nov"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fecha) = 12 THEN 1 ELSE 0 END) AS Dic"),
            );
            $sql = $this->_db_table->select()
                ->from($this->_db_table, $fields)
                ->where("fecha IS NOT NULL")
                ->where("YEAR(fecha) = ?", $year);
            if ($idCliente) {
                $sql->where('idCliente = ?', $idCliente);
            }
            if ($idAduana) {
                $sql->where('idAduana = ?', $idAduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerIncidenciasPorAduanaGrafica($year, $month = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("i" => "incidencias"), array(
                    "a.nombre AS name",
                    "a.abbrv",
                    "a.patente",
                    "a.aduana",
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 1 THEN 1 ELSE 0 END) AS corr"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 2 THEN 1 ELSE 0 END) AS aiq"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 3 THEN 1 ELSE 0 END) AS opeesp"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 4 THEN 1 ELSE 0 END) AS nld"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 5 THEN 1 ELSE 0 END) AS tdq"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 6 THEN 1 ELSE 0 END) AS otro"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 7 THEN 1 ELSE 0 END) AS cliente"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError IS NULL THEN 1 ELSE 0 END) AS nd"),
                    new Zend_Db_Expr("SUM(CASE WHEN i.idTipoError = 99 THEN 1 ELSE 0 END) AS error")
                ))
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = i.idAduana", array())
                ->where("YEAR(i.fecha) = ?", $year)
                ->group("i.idAduana");
            if (isset($month)) {
                $sql->where("MONTH(i.fecha) = ?", $month);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {

                $arrl = [];
                
                foreach ($stmt->toArray() as $value) {
                    if ($value["abbrv"]) {
                        array_push($arrl, $value["abbrv"]);
                    }
                }
                
                $total = $stmt->toArray();
                
                $arr = array(
                    'error' => $this->create_array(count($arrl)),
                    'otro' => $this->create_array(count($arrl)),
                    'corr' => $this->create_array(count($arrl)),
                    'aiq' => $this->create_array(count($arrl)),
                    'opeesp' => $this->create_array(count($arrl)),
                    'nld' => $this->create_array(count($arrl)),
                    'tdq' => $this->create_array(count($arrl)),
                    'cliente' => $this->create_array(count($arrl)),
                    'nd' => $this->create_array(count($arrl)),
                );

                for ($i = 0; $i < count($arrl); $i++) {
                    $arr['error'][$i] = $arr['error'][$i] + (int) $total[$i]['error'];
                    $arr['otro'][$i] = $arr['otro'][$i] + (int) $total[$i]['otro'];
                    $arr['aiq'][$i] = $arr['aiq'][$i] + (int) $total[$i]['aiq'];
                    $arr['opeesp'][$i] = $arr['opeesp'][$i] + (int) $total[$i]['opeesp'];
                    $arr['nld'][$i] = $arr['nld'][$i] + (int) $total[$i]['nld'];
                    $arr['tdq'][$i] = $arr['tdq'][$i] + (int) $total[$i]['tdq'];
                    $arr['cliente'][$i] = $arr['cliente'][$i] + (int) $total[$i]['cliente'];
                    $arr['nd'][$i] = $arr['nd'][$i] + (int) $total[$i]['nd'];
                }

                return array(
                    "labels" => $arrl,
                    "data" => array(
                        array(
                            "name" => 'Ope. Esp.',
                            "data" => $arr['opeesp']
                        ),
                        array(
                            "name" => 'NLD',
                            "data" => $arr['nld']
                        ),
                        array(
                            "name" => 'TDQ',
                            "data" => $arr['tdq']
                        ),
                        array(
                            "name" => 'AIQ',
                            "data" => $arr['aiq']
                        ),
                        array(
                            "name" => 'Cliente',
                            "data" => $arr['cliente']
                        ),
                        array(
                            "name" => 'Error',
                            "data" => $arr['error']
                        ),
                        array(
                            "name" => 'Otro',
                            "data" => $arr['otro']
                        ),
                        array(
                            "name" => 'No especificado',
                            "data" => $arr['nd']
                        )
                    )
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function create_array($num_elements){
        return array_fill(0, $num_elements, 0);
    }
}
