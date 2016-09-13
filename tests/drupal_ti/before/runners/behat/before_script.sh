#!/bin/bash
# Simple script to install drupal for travis-ci running.

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
drupal_ti_ensure_drupal

cd "$DRUPAL_TI_DRUPAL_DIR"

drush dl webform --yes
drush pm-enable webform --yes