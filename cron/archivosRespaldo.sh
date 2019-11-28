#!/bin/bash
YEAR="`date +%Y`"
FILES640="M3_640_`date +%j_%Y%m%d_%H%M%S`.tar.gz"
DIR640="/home/samba-share/expedientes/m3/backups/640/saai/`date +%Y`/`date +%j`"
mkdir -p $DIR640
cd /home/samba-share/archivos_640/saai/$YEAR && tar -cvzf $DIR640/$FILES640 .
FILES240="M3_240_`date +%j_%Y%m%d_%H%M%S`.tar.gz"
DIR240="/home/samba-share/expedientes/m3/backups/240/saai/`date +%Y`/`date +%j`"
mkdir -p $DIR240
cd /home/samba-share/archivos_240/saai/$YEAR && tar -cvzf $DIR240/$FILES240 .
