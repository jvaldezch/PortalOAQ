#!/bin/bash
FILENAME="enviarm3ftp_`date +%Y%m%d_%H%M%S`.log"
curl -ks -m 600 --connect-timeout 600 --request GET "https://127.0.0.1/automatizacion/index/send-to-ftp" > /var/www/portalprod/logs/$FILENAME
