#!/bin/bash
# Simple script to install drupal for travis-ci running.

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
drupal_ti_ensure_drupal

cd "$DRUPAL_TI_DRUPAL_DIR"

#drush dl webform --yes
#drush pm-enable webform --yes

drush dl encryption --yes
drush pm-enable encryption --yes

drush dl contact_block --yes
drush pm-enable contact_block --yes

drush dl contact_storage --yes
drush pm-enable contact_storage --yes

chmod +w $DRUPAL_TI_DRUPAL_DIR/sites/default/settings.php
echo "\$settings['encryption_key'] = 'IPMj1A1H5w+EMrN5a+w3Y8MUv0CsAAPM5OfaGwMOou4=';" >> $DRUPAL_TI_DRUPAL_DIR/sites/default/settings.php
