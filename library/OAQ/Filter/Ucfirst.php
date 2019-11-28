<?php

class OAQ_Filter_Ucfirst implements Zend_Filter_Interface {

    public function filter($value) {
        $valueFiltered = ucfirst($value);
        return $valueFiltered;
    }

}
