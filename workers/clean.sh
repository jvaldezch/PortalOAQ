#!/bin/bash
# Checar si los directorios de respaldo estan creados.
#
find /tmp/ed_* -mtime +1 -type d -exec rm -rf {} \;
if [ ! -d "/home/samba-share/archivos_640/backup/`date +%Y`/" ]; then
  mkdir /home/samba-share/archivos_640/backup/`date +%Y`/
fi
find /home/samba-share/archivos_640/saai/`date +%Y` -mmin +59 -type f -exec mv "{}" /home/samba-share/archivos_640/backup/`date +%Y`/ \;
#find /home/samba-share/archivos_646/saai/`date +%Y` -mmin +59 -type f -exec mv "{}" /home/samba-share/archivos_646/backup/`date +%Y`/ \;
if [ ! -d "/home/samba-share/archivos_240/backup/`date +%Y`/" ]; then
  mkdir /home/samba-share/archivos_240/backup/`date +%Y`/
fi
find /home/samba-share/archivos_240/saai/`date +%Y` -mmin +59 -type f -exec mv "{}" /home/samba-share/archivos_240/backup/`date +%Y`/ \;
find /tmp/edoctmp/*.xml -mmin +59 -type f -exec rm {} \;
find /tmp/ed_* -mtime +1 -type d -exec rm -rf {} \;
find /tmp/zips/*.zip -mtime +1 -type f -exec rm {} \;
find /var/www/portalprod/logs/*.log -mtime +5 -type f -exec rm {} \;

