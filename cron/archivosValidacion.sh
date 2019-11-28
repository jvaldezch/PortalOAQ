#!/bin/bash
# chown www-data:www-data -R /home/samba-share/archivos_646/saai/*
chown www-data:www-data -R /home/samba-share/archivos_640/saai/*
chown www-data:www-data -R /home/samba-share/archivos_240/saai/*
FILENAME="archivosValidacion_`date +%j_%Y%m%d_%H%M%S`.log"
curl -ks -m 600 --connect-timeout 600 "https://127.0.0.1/automatizacion/index/archivos-validacion?debug=true&plain=true" > /var/www/portalprod/logs/$FILENAME
