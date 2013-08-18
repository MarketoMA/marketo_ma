<?php

/**
 * @file
 * Drush integration for Marketo MA
 */

/**
 * Implements hook_drush_command().
 */
function marketo_ma_drush_command() {
  $items = array();
  $items['mma-get-lead'] = array(
    'description' => dt('Get Marketo lead information for the specified user or email address.'),
    'arguments' => array(
      'user' => dt('A uid, user name, or email address.'),
    ),
    'examples' => array(
      'drush mma-get-lead 14' => dt('Get lead data for user account 14.'),
      'drush mma-get-lead john.doe' => dt('Get lead data for user john.doe.'),
      'drush mmal john@example.com' => dt('Get lead data associated with email address john@example.com.'),
    ),
    'aliases' => array('mmal'),
  );
  $items['mma-get-activity'] = array(
    'description' => dt('Get Marketo activity for the specified lead.'),
    'arguments' => array(
      'user' => dt('A uid, user name, or email address.'),
    ),
    'examples' => array(
      'drush mma-get-activity 14' => dt('Get activity events for user account 14.'),
      'drush mma-get-activity john.doe' => dt('Get activity events for user john.doe.'),
      'drush mmaa john@example.com' => dt('Get activity events associated with email address john@example.com.'),
    ),
    'aliases' => array('mmaa'),
  );
  $items['mma-fields'] = array(
    'description' => dt('Get Marketo fields.'),
    'aliases' => array('mmaf'),
  );
  $items['mma-verify'] = array(
    'description' => dt('Verify this site can connect to Marketo.'),
    'aliases' => array('mmav'),
  );
  return $items;
}

function drush_marketo_ma_mma_get_lead($user = 1) {
  $rows[] = array(dt('Group'), dt('Key'), dt('Value'));
  $uid = _drush_user_get_uid($user);
  if ($uid !== FALSE) {
    $account = user_load($uid);
    $fields = array('uid', 'name', 'mail');
    foreach ($fields as $field) {
      $rows[] = array(dt('Account'), $field, $account->$field);
    }
    $marketo = marketo_ma_get_lead($account->mail);
  }
  else if (valid_email_address($user)) {
    $marketo = marketo_ma_get_lead($user);
  }
  if (!empty($marketo)) {
    $rows[] = array(dt('Marketo'), 'Id', $marketo[0]->Id);
    foreach ($marketo[0]->attributes as $key => $value) {
      $rows[] = array(dt('Marketo'), $key, $value);
    }
  }

  if (count($rows) > 1) {
    drush_print_table($rows);
  }
}

function drush_marketo_ma_mma_get_activity($user = 1) {
  $rows[] = array(dt('ID'), dt('Date/Time'), dt('Activity Type'), dt('Asset Name'));
  $uid = _drush_user_get_uid($user);
  if ($uid !== FALSE) {
    $account = user_load($uid);
    $activity = marketo_ma_get_lead_activity($account->mail);
  }
  else if (valid_email_address($user)) {
    $activity = marketo_ma_get_lead_activity($user);
  }

  if (!empty($activity)) {
    $fields = array('id', 'activityDateTime', 'activityType', 'mktgAssetName');
    foreach ($activity as $event) {
      $row = array();
      foreach ($fields as $field) {
        $row[] = $event->$field;
      }
      $rows[] = $row;
    }
  }

  if (count($rows) > 1) {
    drush_print_table($rows);
  }
}

function drush_marketo_ma_mma_fields() {
  $rows[] = array(dt('Name'), dt('Label'));
  $fields = _marketo_ma_get_fields();
  foreach ($fields as $key => $value) {
    $rows[] = array($key, $value);
  }
  drush_print_table($rows);
}

function drush_marketo_ma_mma_verify() {
  $fields = _marketo_ma_get_fields();
  if (count($fields) > 0) {
    drush_log('Successfully connected to Marketo', 'success');
  }
  else {
    drush_log('Unable to connect to Marketo', 'error');
  }
}
