<?php

class Application_Model_ModuleMapper
{
    protected $_db;
    
    public function __construct()
    {
        $this->_db = Zend_Registry::get('oaqintranet');
    }    
    /**
     * 
     * @param int $modId
     * @return String
     */
    public function getModuleName($modId)
    {   
        $select = $this->_db->select()
                ->from('modulos',array('nombre'))
                ->where('id = ?',$modId);
        
        $result = $this->_db->fetchRow($select,array());
        
        if($result) {            
            return $result['nombre'];
        }
        return NULL;
    }
    
    /**
     * 
     * @param String $modName
     * @return int
     */
    public function getModuleId($modName)
    {
        $select = $this->_db->select()
                ->from('modulos',array('id'))
                ->where('modulo LIKE ?',$modName);
        
        $result = $this->_db->fetchRow($select,array());
        
        if($result) {            
            return $result['id'];
        }
        return NULL;
    }
    
}