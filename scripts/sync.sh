#!/bin/bash
#Sync script from prod to local only (activates dev modules)

#To use this script you should have web/drush/sites/mmp.site.yml file :
#prod:
#  host: meetmypsy.linagora.com
#  user: myunixusername
#  root: /var/www/html/mmp/MeetMyPsy/web
#  uri: http://meetmypsy.linagora.com
#

echo 'Starts execution at:' `date -d now +%Hh:%Mmin:%Ssec`
START_TIME=`date -d now +%s`

SCRIPT_FILE=$(readlink -f "$0")
SCRIPTS=$(dirname "$SCRIPT_FILE")
#Go to drupal root
cd $SCRIPTS/../web

eval `ssh-agent -s`
ssh-add ~/.ssh/id_rsa
sudo rm -rf sites/default/files/*

drush -y sql-sync @mmp.prod @self
drush -y rsync @mmp.prod:%files/ @self:%files
#Enable dev modules
drush -y en devel potx
drush -y cset devel.settings devel_dumper kint
drush cr
drush cex -y
git st

#Print the runtime
END_TIME=`date -d now +%s`
RUNTIME=$((END_TIME-START_TIME))
COUNT_MINUTES=60;
MINUTES=$((RUNTIME / COUNT_MINUTES))
SECONDS=$((RUNTIME % COUNT_MINUTES))
echo 'Finished execution at:' `date -d now +%Hh:%Mmin:%Ssec`
echo 'Runtime: '$MINUTES'min'$SECONDS'sec'
