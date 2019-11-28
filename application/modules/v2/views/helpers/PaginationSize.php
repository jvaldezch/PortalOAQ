<?php

class Zend_View_Helper_PaginationSize extends Zend_View_Helper_Abstract {

    public function paginationSize($value) {        
        return '<select name="pagination-size" id="pagination-size" class="traffic-pagination-size">'
        . '<option value="10"' . ((isset($value) && $value == 10) ? 'selected="selected"' : '' ) . '>10</option>'
        . '<option value="20"' . ((isset($value) && $value == 20) ? 'selected="selected"' : '' ) . '>20</option>'
        . '<option value="30"' . ((isset($value) && $value == 30) ? 'selected="selected"' : '' ) . '>30</option>'
        . '<option value="40"' . ((isset($value) && $value == 40) ? 'selected="selected"' : '' ) . '>40</option>'
        . '<option value="50"' . ((isset($value) && $value == 50) ? 'selected="selected"' : '' ) . '>50</option>'
        . '</select>';
    }

}
