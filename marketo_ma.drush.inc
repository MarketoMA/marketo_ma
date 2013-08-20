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
      'key' => dt('A uid, user name, or email address. If using --remote this should be an IDNUM, EMAIL, or COOKIE Marketo value.'),
    ),
    'examples' => array(
      'drush mma-get-lead 14' => dt('Get lead data for user account 14.'),
      'drush mmal john@example.com' => dt('Search for account john@example.com locally then try to match to a Marketo record.'),
      'drush mmal john@example.com --remote' => dt('Search Marketo for john@example.com then match to a local account if possible.'),
    ),
    'options' => array(
      'remote' => dt('Search Marketo then try to match a local user account.'),
    ),
    'aliases' => array('mmal'),
  );
  $items['mma-get-activity'] = array(
    'description' => dt('Get Marketo activity for the specified lead.'),
    'arguments' => array(
      'key' => dt('A uid, user name, or email address. If using --remote this should be an IDNUM, EMAIL, or COOKIE Marketo value.'),
    ),
    'examples' => array(
      'drush mma-get-activity 14' => dt('Get activity events for user account 14.'),
      'drush mma-get-activity john.doe' => dt('Get activity events for user john.doe.'),
      'drush mmaa john@example.com' => dt('Get activity events associated with email address john@example.com.'),
    ),
    'options' => array(
      'remote' => dt('Search Marketo then try to match a local user account.'),
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

function drush_marketo_ma_mma_get_lead($key = NULL) {
  if (is_null($key)) {
    drush_log('No key provided', 'error');
    return;
  }
  $rows[] = array(dt('Group'), dt('Key'), dt('Value'));
  $remote = drush_get_option('remote', FALSE);
  if ($remote) {
    $marketo = marketo_ma_get_lead($key);
    if (!empty($marketo)) {
      $account = user_load_by_mail($marketo[0]->Email);
    }
  }
  else {
    $uid = _drush_user_get_uid($key);
    if ($uid !== FALSE) {
      $account = user_load($uid);
      $marketo = marketo_ma_get_lead($account->mail);
    }
  }

  if ($account) {
    $fields = array('uid', 'name', 'mail');
    foreach ($fields as $field) {
      $rows[] = array(dt('Account'), $field, $account->$field);
    }
  }

  if (!empty($marketo)) {
    $rows[] = array(dt('Marketo'), 'Id', $marketo[0]->Id);
    $rows[] = array(dt('Marketo'), 'Email', $marketo[0]->Email);
    foreach ($marketo[0]->attributes as $key => $value) {
      $rows[] = array(dt('Marketo'), $key, $value);
    }
  }

  if (count($rows) > 1) {
    drush_print_table($rows);
  }
}

function drush_marketo_ma_mma_get_activity($key = NULL) {
  if (is_null($key)) {
    drush_log('No key provided', 'error');
    return;
  }
  $rows[] = array(dt('ID'), dt('Date/Time'), dt('Activity Type'), dt('Asset Name'));
  $remote = drush_get_option('remote', FALSE);
  if ($remote) {
    $activity = marketo_ma_get_lead_activity($key);
  }
  else {
    $uid = _drush_user_get_uid($key);
    if ($uid !== FALSE) {
      $account = user_load($uid);
      $activity = marketo_ma_get_lead_activity($account->mail);
    }
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
