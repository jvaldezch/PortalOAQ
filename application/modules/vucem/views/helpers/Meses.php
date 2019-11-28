<?php

class Zend_View_Helper_Meses extends Zend_View_Helper_Abstract {

    public function meses($aduana = null, $data = null) {
        if ($aduana) {
            $html = '';
            foreach (range(1, 12) as $month) {
                $found = false;
                foreach ($data as $mes) {
                    if ($mes['mes'] == $this->fixMonth($month) && $aduana == $mes['aduana']) {
                        $html .= '<td>' . $mes['total'] . '</td>';
                        $found = true;
                    }
                }
                if ($found == false) {
                    $html .= '<td>&nbsp;</td>';
                }
            }
        } else {
            $html = '';
            foreach (range(1, 12) as $month) {
                $html .= '<th>' . $this->monthName($this->fixMonth($month)) . '</th>';
            }
        }
        return $html;
    }

    protected function fixMonth($month) {
        return str_pad($month, 2, '0', STR_PAD_LEFT);
    }

    protected function monthName($month) {
        switch ($month) {
            case '01':
                return 'Ene.';
            case '02':
                return 'Feb.';
            case '03':
                return 'Mar.';
            case '04':
                return 'Abr.';
            case '05':
                return 'May.';
            case '06':
                return 'Jun.';
            case '07':
                return 'Jul.';
            case '08':
                return 'Ago.';
            case '09':
                return 'Sept.';
            case '10':
                return 'Oct.';
            case '11':
                return 'Nov.';
            case '12':
                return 'Dic.';
            default:
                return $month;
        }
    }

}
