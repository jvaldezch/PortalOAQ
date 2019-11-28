#!/bin/bash
FILENAME="cuentasGastos_`date +%Y%m%d_%H%M%S`.log"
curl -ks -m 600 --connect-timeout 600 "https://127.0.0.1/automatizacion/archive/get-invoices?debug=true" > /var/www/portalprod/logs/$FILENAME
