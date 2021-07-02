#!/bin/bash
#Update script

echo 'Starts execution at:' `date -d now +%Hh:%Mmin:%Ssec`
START_TIME=`date -d now +%s`

SCRIPT_FILE=$(readlink -f "$0")
SCRIPTS=$(dirname "$SCRIPT_FILE")
#Go to project root
cd $SCRIPTS/../

git pull --rebase

#execute update actions
drush deploy -v -y

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

#Print the runtime
END_TIME=`date -d now +%s`
RUNTIME=$((END_TIME-START_TIME))
COUNT_MINUTES=60;
MINUTES=$((RUNTIME / COUNT_MINUTES))
SECONDS=$((RUNTIME % COUNT_MINUTES))
echo 'Finished execution at:' `date -d now +%Hh:%Mmin:%Ssec`
echo 'Runtime: '$MINUTES'min'$SECONDS'sec'
