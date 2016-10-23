#!/bin/bash

# Add an optional statement to see that this is running in Travis CI.
echo "running drupal_ti/before/before_script.sh"

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# The first time this is run, it will install Drupal.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Change to the Drupal directory
cd "$DRUPAL_TI_DRUPAL_DIR"

# Create the the module directory (only necessary for D7)
# For D7, this is sites/default/modules
# For D8, this is modules
mkdir -p "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH"
cd "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH"

#drush dl webform --yes
#drush pm-enable webform --yes

drush dl encryption --yes
drush pm-enable encryption --yes

drush dl contact_block --yes
drush pm-enable contact_block --yes

if [ "$DRUPAL_TI_CORE_BRANCH" = "8.2.x" ]
then
    drush dl contact_storage --yes
    drush pm-enable contact_storage --yes
fi

chmod +w $DRUPAL_TI_DRUPAL_DIR/sites/default/settings.php
echo "\$settings['encryption_key'] = 'IPMj1A1H5w+EMrN5a+w3Y8MUv0CsAAPM5OfaGwMOou4=';" >> $DRUPAL_TI_DRUPAL_DIR/sites/default/settings.php
