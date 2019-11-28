<?php

class Archivo_Model_PidMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_Pid();
    }

    public function addNewProcess($pid, $file, $command) {
        try {
            $data = array(
                'pid' => $pid,
                'file' => $file,
                'command' => $command,
            );
            $this->_db_table->insert($data);
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>:" . $e->getMessage();
            die();
        }
    }

    public function checkRunnigProcess($file) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array('pid'))
                    ->where("file = ?", $file);
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                $data = $result->toArray();
                return $data;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>:" . $e->getMessage();
            die();
        }
    }

    public function deleteProcess($pid) {
        try {
            $where = array("pid = ?" => $pid);
            $this->_db_table->delete($where);
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>:" . $e->getMessage();
            die();
        }
    }

}
