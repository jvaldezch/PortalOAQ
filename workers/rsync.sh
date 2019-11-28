#!/bin/bash
FILENAME="rsync_`date +%Y%m%d_%H%M%S`.log"
rsync -avr --backup --suffix=_`date +"%m%d%Y_%H%M"` /home/samba-share/expedientes/ /mnt/nas/expedientes/ > /var/www/portalprod/logs/$FILENAME
