<?php

class Zend_View_Helper_Download extends Zend_View_Helper_Abstract {

    public function download($id, $filename) {
        $arch = new Archivo_Model_RepositorioMapper();
        $info = $arch->obtenerInformacion($id);

        if ($info["edocument"] != null && $info["tipo_archivo"] == 27) {
            return '<a class="openfile" href="/archivo/index/load-file-repo?id=' . $id . '"><img src="/images/icons/open-file.png" /></a>'
                    . '&nbsp;|&nbsp;'
                    . '<a href="#"><img src="/images/icons/trash.png" /></a>';
        } elseif ($info["edocument"] == null) {
            return '<a class="openfile" href="/archivo/index/load-file-repo?id=' . $id . '"><img src="/images/icons/open-file.png" /></a>'
                    . '&nbsp;|&nbsp;'
                    . '<a href="#"><img src="/images/icons/trash.png" /></a>';
        } else {
            return '&nbsp;';
        }
    }

}
