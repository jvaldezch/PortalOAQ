#!/bin/bash
BACKUP="oaqintranet_`date +%Y%m%d_%H%M%S`.tar.bz2"
tar -cpzf /home/jvaldez/backups/$BACKUP --exclude=".svn" --exclude="*.log" /var/www/portalprod/

