#!/bin/bash
#Installation script

echo 'Starts execution at:' `date -d now +%Hh:%Mmin:%Ssec`
START_TIME=`date -d now +%s`

SCRIPT_FILE=$(readlink -f "$0")
SCRIPTS=$(dirname "$SCRIPT_FILE")
#Go to project root
cd $SCRIPTS/../

#Delete database if exists
mysql --defaults-extra-file=$SCRIPTS/mysql_config -e "DROP DATABASE IF EXISTS mmp";
#Create new database
mysql --defaults-extra-file=$SCRIPTS/mysql_config -e "CREATE DATABASE mmp CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
#Install Drupal
PASSWORD=$(openssl rand -base64 8)
drush -y si --account-pass=$PASSWORD
#Override site id
drush -y config-set "system.site" uuid "0916921c-d7ce-44e8-b96b-de70c29d2623"
#Delete content entities
drush entity:delete shortcut_set
#Import configuration
drush -y cim
#Rebuild permissions
drush php-eval 'node_access_rebuild();'
#Update contrib translations
drush locale:update
#Import custom translations
for file in $(ls ./po/*.po);
do
  file_path=$(readlink -f $file)
  echo
  echo 'Importing from '$file_path
  drush locale:import fr $file_path --type=customized --override=all
done

#print passsword
echo
echo "Installed with password : "$PASSWORD
echo
#Print the runtime
END_TIME=`date -d now +%s`
RUNTIME=$((END_TIME-START_TIME))
COUNT_MINUTES=60;
MINUTES=$((RUNTIME / COUNT_MINUTES))
SECONDS=$((RUNTIME % COUNT_MINUTES))
echo 'Finished execution at:' `date -d now +%Hh:%Mmin:%Ssec`
echo 'Runtime: '$MINUTES'min'$SECONDS'sec'
