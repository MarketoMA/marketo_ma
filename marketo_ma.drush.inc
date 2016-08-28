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
    'description' => dt('Get Marketo lead and activity information for the given search criteria.'),
    'arguments' => array(
      'key' => dt('An email address, Marketo lead ID, Marketo _mkto_trk cookie, Drupal uid, or Drupal name'),
    ),
    'examples' => array(
      'drush mma-get-lead john@example.com' => dt('Get leads with email address john@example.com.'),
      'drush mmal john@example.com --activity' => dt('Get leads with email address john@example.com and include activity records.'),
      'drush mmal john@example.com --activity --info=0' => dt('Show only activity for john@example.com'),
      'drush mmal john@example.com --local' => dt('Get leads with email address john@example.com that also have a Drupal user account.'),
      'drush mma-get-lead 14' => dt('Get leads for Marketo lead ID 14 or Drupal uid 14 and return merged results.'),
      'drush mmal 14 --local' => dt('Get leads for Drupal uid 14 and ignores Marketo Lead ID 14.'),
      'drush mmal 14 --remote' => dt('Get lead for Marketo lead ID 14 and ignores Drupal uid 14'),
      'drush mmal "id:222-AAA-222&token:_mch-example.com-1384222270733-22222"' => dt('Get lead by _mkto_trk value.'),
    ),
    'options' => array(
      'activity' => dt('Show lead activity (OFF by default).'),
      'info' => dt('Show lead information (ON by default). Hide with --info=0'),
      'local' => dt('Search only Drupal keys (mail, uid, name).'),
      'remote' => dt('Search only Marketo keys (mail, lead ID, cookie)'),
    ),
    'aliases' => array('mmal'),
  );
  $items['mma-fields'] = array(
    'description' => dt('Get Marketo fields.'),
    'aliases' => array('mmaf'),
    'outputformat' => array(
      'default' => 'table',
      'pipe-format' => 'list',
      'field-labels' => array(
        'name' => 'name',
        'displayName' => 'displayName',
        'dataType' => 'dataType',
        'length' => 'length',
        'readOnly' => 'readOnly',
        'id' => 'id',
        'soapName' => 'soapName',
        'soapReadOnly' => 'soapReadOnly',
      ),
      'fields-default' => array('name', 'displayName'),
      'output-data-type' => 'format-table',
    ),
  );
  $items['mma-verify'] = array(
    'description' => dt('Verify this site can connect to Marketo.'),
    'aliases' => array('mmav'),
  );
  return $items;
}

/**
 * Callback function for mma-get-lead.
 *
 * @param string $key
 */
function drush_marketo_ma_mma_get_lead($key = NULL) {
  if (is_null($key)) {
    drush_log('No key provided', 'error');
    return;
  }

  $local = (bool) drush_get_option('local', FALSE);
  $remote = (bool) drush_get_option('remote', FALSE);
  if ($local && $remote) {
    drush_log('--local and --remote cannot be used together', 'error');
    return;
  }

  if ($remote) {
    $scope = 'marketo';
  }
  elseif ($local) {
    $scope = 'drupal';
  }
  else {
    $scope = strtolower(drush_get_option('scope', 'all'));
  }

  $info = (bool) drush_get_option('info', TRUE);
  $activity = (bool) drush_get_option('activity', FALSE);

  $finds = array();

  switch ($scope) {
    case 'all':
      $finds = _marketo_ma_search_drupal_lead($key, $activity) + _marketo_ma_search_marketo_lead($key, $activity);
      break;

    case 'drupal':
      $finds = _marketo_ma_search_drupal_lead($key, $activity);
      break;

    case 'marketo':
      $finds = _marketo_ma_search_marketo_lead($key, $activity);
      break;

    default:
      drush_log('Valid scope options are: all, drupal, marketo', 'error');
      return;
  }

  switch (count($finds)) {
    case 0:
      drush_log("No leads found for $key", 'warning');
      break;

    case 1:
      drush_log("Lead found for $key: [" . implode(",", array_keys($finds)) . "]", 'success');
      break;

    default:
      drush_log("Multiple leads found for $key: [" . implode(",", array_keys($finds)) . "]", 'success');
      break;
  }

  foreach ($finds as $leadid => $find) {
    if ($info) {
      drush_print('Marketo lead ID ' . $find['marketo']->Id);
      $rows = array();
      $rows[] = array(dt('Group'), dt('Key'), dt('Value'));
      if ($find['drupal']->uid) {
        $rows[] = array(dt('Account'), 'uid', $find['drupal']->uid);
        $rows[] = array(dt('Account'), 'name', $find['drupal']->name);
        $rows[] = array(dt('Account'), 'mail', $find['drupal']->mail);
      }
      $rows[] = array(dt('Marketo'), 'Id', $find['marketo']->Id);
      $rows[] = array(dt('Marketo'), 'Email', $find['marketo']->Email);
      // Process Marketo attributes.
      foreach ($find['marketo']->attributes as $attr => $value) {
        $rows[] = array(dt('Marketo'), $attr, $value);
      }
      drush_print_table($rows, TRUE);
    }

    if ($activity && array_key_exists('activity', $find)) {
      if (count($find['activity'])) {
        drush_print('Activity for lead ID ' . $find['marketo']->Id);
        _marketo_ma_print_activity_($find['activity']);
      }
      else {
        drush_print('No activity found for lead ID ' . $find['marketo']->Id);
      }
    }
  }

  return;
}

/**
 * Callback function for mma-fields.
 */
function drush_marketo_ma_mma_fields() {
  $fields = _marketo_ma_get_fields();
  foreach ($fields as $key => $value) {
    $rows[$value['name']] = $value;
  }
  return $rows;
}

/**
 * Callback function for mma-verify.
 */
function drush_marketo_ma_mma_verify() {
  $fields = _marketo_ma_get_fields();
  if (count($fields) > 0) {
    drush_log('Successfully connected to Marketo', 'success');
  }
  else {
    drush_log('Unable to connect to Marketo', 'error');
  }
}

/**
 * Get uid(s) from a uid, user name, or email address.
 * 
 * @param string $search
 *   A uid, user name, or email address
 * 
 * @return array
 *   Associative array or records found keyed on uid
 */
function _marketo_ma_get_uid($search) {
  // We use a DB query while looking for the uid to keep things speedy.
  $uids = array();
  if (is_numeric($search)) {
    $query = db_query("SELECT uid, name, mail FROM {users} WHERE uid = :uid OR name = :name", array(':uid' => $search, ':name' => $search));
  }
  else {
    $query = db_query("SELECT uid, name, mail FROM {users} WHERE mail = :mail OR name = :name", array(':mail' => $search, ':name' => $search));
  }
  return $query->fetchAllAssoc('uid');
}

function _marketo_ma_search_marketo_lead($key, $activity = FALSE) {
  $result = array();
  $marketo = marketo_ma_get_lead($key); // Can return multiple.
  foreach ($marketo as $lead) {
    $account = user_load_by_mail($lead->Email); // Not sure about multiple.
    $result[$lead->Id] = array(
      'marketo' => $lead,
      'drupal' => $account,
    );
    if ($activity) {
      $result[$lead->Id]['activity'] = marketo_ma_get_lead_activity($lead->Id);
    }
  }

  return $result;
}

function _marketo_ma_search_drupal_lead($key, $activity = FALSE) {
  $result = array();
  $uids = _marketo_ma_get_uid($key); // Can return multiple.
  foreach ($uids as $uid => $account) {
    $marketo = marketo_ma_get_lead($account->mail);
    foreach ($marketo as $lead) {
      $result[$lead->Id] = array(
        'marketo' => $lead,
        'drupal' => $account,
      );
      if ($account) {
        $result[$lead->Id]['activity'] = marketo_ma_get_lead_activity($lead->Id);
      }
    }
  }

  return $result;
}

function _marketo_ma_print_activity_($activity) {
  $fields = array('id', 'activityDateTime', 'activityType', 'mktgAssetName');
  $rows = array();
  $rows[] = array(dt('ID'), dt('Date/Time'), dt('Activity Type'), dt('Asset Name'));
  foreach ($activity as $event) {
    $row = array();
    foreach ($fields as $field) {
      $row[] = $event->$field;
    }
    $rows[] = $row;
  }

  drush_print_table($rows, TRUE);
}
