<?php

class OAQ_Validate_UploadZip extends Zend_Validate_File_Upload {

    protected $_messageTemplates = array(
        self::FILE_NOT_FOUND => 'No se encuentra el archivo seleccionado',
        self::NO_FILE => 'No ha seleccionado un archivo'
    );

}
