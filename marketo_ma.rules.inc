<?php
/**
 * @file
 * Rules integration for Marketo MA
 */

/**
 * Implements hook_rules_action_info().
 */
function marketo_ma_rules_action_info() {
  $actions = array(
    'add_lead' => array(
      'label' => t('Add data to Marketo lead.'),
      'parameter' => array(
        'account' => array(
          'type' => 'user',
          'label' => t('User account to add as Marketo lead.'),
          'save' => FALSE,
        ),
        'data' => array(
          'type' => 'list',
          'label' => t('Marketo data should be a list of pipe delimited strings provided by a variable.'),
        ),
      ),
      'group' => t('Marketo'),
      'base' => 'marketo_ma_actions_add_lead',
    ),
  );
  return $actions;
}


/**
 * Process data passed as a list from action firing and execute Marketo lead update.
 */
function marketo_ma_actions_add_lead($account, $data) {

  $output = array();
  foreach ($data as $row) {
    $r = explode("|", $row);
    $output[$r[0]] = $r[1];
  }
  if ($account->mail) {
    marketo_ma_add_lead($account->mail, $output);
  }
}