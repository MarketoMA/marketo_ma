<?php

/**
 * @file
 * Administrative page callbacks for the Marketo MA module.
 */

/**
 * Builds and returns the Marketo settings form.
 */
function marketo_ma_admin_settings_form($form, &$form_state) {
  $form['marketo_ma_basic'] = array(
    '#title' => t('Basic Settings'),
    '#type' => 'fieldset',
  );

  $form['marketo_ma_basic']['marketo_ma_munchkin_account_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Account ID'),
    '#default_value' => variable_get('marketo_ma_munchkin_account_id', ''),
    '#required' => TRUE,
  );
  $form['marketo_ma_basic']['marketo_ma_munchkin_javascript_library'] = array(
    '#type' => 'textfield',
    '#title' => t('Munchkin Javascript Library'),
    '#default_value' => variable_get('marketo_ma_munchkin_javascript_library', '//munchkin.marketo.net/munchkin.js'),
    '#required' => TRUE,
    '#description' => t('Typically this does not need to be changed and should use the default value //munchkin.marketo.net/munchkin.js'),
  );
  $form['marketo_ma_basic']['marketo_ma_instance_host'] = array(
    '#type' => 'textfield',
    '#title' => t('Marketo Instance Host'),
    '#default_value' => variable_get('marketo_ma_instance_host'),
    '#required' => FALSE,
    '#description' => t('Host for your Marketo instance. Example: app-sjqe.marketo.com'),
  );
  $form['marketo_ma_basic']['marketo_ma_munchkin_lead_source'] = array(
    '#type' => 'textfield',
    '#title' => t('Default Lead Source'),
    '#default_value' => variable_get('marketo_ma_munchkin_lead_source'),
    '#description' => t('If set, LeadSource will be set to this value unless specifically overridden during data collection.'),
  );
  $form['marketo_ma_basic']['marketo_ma_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Verbose Logging'),
    '#default_value' => variable_get('marketo_ma_logging', FALSE),
    '#description' => t('If checked, additional data will be added to watchdog.'),
  );

  $form['marketo_ma_tabs'] = array(
    '#type' => 'vertical_tabs',
  );

  $form['marketo_ma_tabs']['marketo_ma_api'] = array(
    '#title' => t('API Configuration'),
    '#type' => 'fieldset',
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_tracking_method'] = array(
    '#title' => t('Tracking Method'),
    '#type' => 'radios',
    '#options' => array(
      'munchkin' => t('Munchkin Javascript API'),
      'rest' => t('REST API (Synchronous)'),
      'rest_async' => t('REST API (Asynchronous)'),
    ),
    '#default_value' => variable_get('marketo_ma_tracking_method', MARKETO_MA_TRACKING_METHOD_DEFAULT),
    '#required' => TRUE,
    '#descritpion' => t('Asynchronous will queue requests and send data when cron runs.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin'] = array(
    '#title' => t('Munchkin Javascript API'),
    '#type' => 'fieldset',
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_api_private_key'] = array(
    '#type' => 'textfield',
    '#title' => t('API Private Key'),
    '#default_value' => variable_get('marketo_ma_munchkin_api_private_key', ''),
    '#required' => TRUE,
    '#description' => t('Value can be found on the Munchkin Admin page at Admin > Integration > Munchkin'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced'] = array(
    '#title' => t('Advanced Initialization Parameters'),
    '#type' => 'fieldset',
    '#description' => t("Munchkin can accept a variety of additional configuration parameters to customize its behavior.<br />NOTE: Leave the field blank to accept it's default value as defined in munchkin.js"),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_partition'] = array(
    '#type' => 'textfield',
    '#title' => t('wsInfo - Workspace (Partition)'),
    '#default_value' => variable_get('marketo_ma_munchkin_partition', ''),
    '#required' => FALSE,
    '#description' => t('Takes a string to target a workspace.  This workspace ID is obtained by selecting the Workspace in the Admin -> Munchkin menu.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_altIds'] = array(
    '#type' => 'textfield',
    '#title' => t('altIds'),
    '#default_value' => variable_get('marketo_ma_munchkin_altIds', ''),
    '#required' => FALSE,
    '#description' => t('Accepts an array of Munchkin ID strings.  When enabled, this will duplicate all Web Activity to the targeted subscriptions, based on their Munchkin Id.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_cookieLifeDays'] = array(
    '#type' => 'textfield',
    '#title' => t('cookieLifeDays'),
    '#default_value' => variable_get('marketo_ma_munchkin_cookieLifeDays', ''),
    '#required' => FALSE,
    '#description' => t('Sets the expiry date of any newly created munchkin tracking cookies to this many days in the future. Default is two years.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_clickTime'] = array(
    '#type' => 'textfield',
    '#title' => t('clickTime'),
    '#default_value' => variable_get('marketo_ma_munchkin_clickTime', ''),
    '#required' => FALSE,
    '#description' => t('Sets the number of miliseconds to block after a click to allow for click tracking request.  Reducing will reduce accuracy of click-tracking.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_cookieAnon'] = array(
    '#type' => 'textfield',
    '#title' => t('cookieAnon'),
    '#default_value' => variable_get('marketo_ma_munchkin_cookieAnon', ''),
    '#required' => FALSE,
    '#description' => t('Default true. If set to false, will prevent tracking and cookying of new anonymous leads.  Leads are cookied and tracked after filling out a Marketo form, or clicking through from a Marketo Email.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_domainLevel'] = array(
    '#type' => 'textfield',
    '#title' => t('domainLevel'),
    '#default_value' => variable_get('marketo_ma_munchkin_domainLevel', ''),
    '#required' => FALSE,
    '#description' => t('Default 3.  Setting to 2 allows for proper tracking on two-letter top-level domains.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_disableClickDelay'] = array(
    '#type' => 'textfield',
    '#title' => t('disableClickDelay'),
    '#default_value' => variable_get('marketo_ma_munchkin_disableClickDelay', ''),
    '#required' => FALSE,
    '#description' => t('Default false.  If set to true, disables click tracking delay entirely.  Will reduce accuracy of click tracking.'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_munchkin']['marketo_ma_munchkin_advanced']['marketo_ma_munchkin_asyncOnly'] = array(
    '#type' => 'textfield',
    '#title' => t('asyncOnly'),
    '#default_value' => variable_get('marketo_ma_munchkin_asyncOnly', ''),
    '#required' => FALSE,
    '#description' => t('Default false.  If set to true, will send XHRs asynchronously.'),
  );

  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest'] = array(
    '#title' => t('REST API'),
    '#type' => 'fieldset',
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_endpoint'] = array(
    '#type' => 'textfield',
    '#title' => t('REST endpoint'),
    '#default_value' => variable_get('marketo_ma_rest_endpoint', ''),
    '#required' => FALSE,
    '#description' => t('Value can be found on the REST API Admin page at Admin > Integration > Web Services'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_identity'] = array(
    '#type' => 'textfield',
    '#title' => t('REST identity'),
    '#default_value' => variable_get('marketo_ma_rest_identity', ''),
    '#required' => FALSE,
    '#description' => t('Value can be found on the REST API Admin page at Admin > Integration > Web Services'),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_client_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Client ID'),
    '#default_value' => variable_get('marketo_ma_rest_client_id', ''),
    '#required' => FALSE,
    '#description' => t('Client ID is established as part of a <a href="@url">Custom Service</a>.', array('@url' => url('http://developers.marketo.com/documentation/rest/custom-service/'))),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_client_secret'] = array(
    '#type' => 'textfield',
    '#title' => t('Client Secret'),
    '#default_value' => variable_get('marketo_ma_rest_client_secret', ''),
    '#required' => FALSE,
    '#description' => t('Client Secret is established as part of a <a href="@url">Custom Service</a>.', array('@url' => url('http://developers.marketo.com/documentation/rest/custom-service/'))),
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_proxy'] = array(
    '#title' => t('REST Client Proxy Settings'),
    '#type' => 'fieldset',
    '#description' => t('For making an HTTP connection through a proxy server, the options proxy_host, proxy_port, proxy_login and proxy_password are also available.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_proxy']['marketo_ma_rest_proxy_host'] = array(
    '#type' => 'textfield',
    '#title' => t('Proxy Host'),
    '#default_value' => variable_get('marketo_ma_rest_proxy_host', ''),
    '#required' => FALSE,
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_proxy']['marketo_ma_rest_proxy_port'] = array(
    '#type' => 'textfield',
    '#title' => t('Proxy Port'),
    '#default_value' => variable_get('marketo_ma_rest_proxy_port', ''),
    '#required' => FALSE,
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_proxy']['marketo_ma_rest_proxy_login'] = array(
    '#type' => 'textfield',
    '#title' => t('Proxy Login'),
    '#default_value' => variable_get('marketo_ma_rest_proxy_login', ''),
    '#required' => FALSE,
  );
  $form['marketo_ma_tabs']['marketo_ma_api']['marketo_ma_rest']['marketo_ma_rest_proxy']['marketo_ma_rest_proxy_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Proxy Password'),
    '#default_value' => variable_get('marketo_ma_rest_proxy_password', ''),
    '#required' => FALSE,
  );

  $form['marketo_ma_tabs']['marketo_ma_fields'] = array(
    '#title' => t('Field Definition'),
    '#type' => 'fieldset',
    '#description' => t('By default, all fields below will be available for mapping to Webform and User Profile fields. It is possible to limit the available fields by selecting them below. Read-only fields are displayed here but are never available for mapping.'),
  );

  if (isset($form_state['triggering_element']['#array_parents']) && in_array('marketo_ma_lead_fields_button', $form_state['triggering_element']['#array_parents'])) {
    // Get a fresh set of field data and cache the results
    $field_data = _marketo_ma_get_fields(FALSE, TRUE);
  }
  else {
    // Get field data from cache
    $field_data = _marketo_ma_get_fields(TRUE);
  }
  $field_defaults = variable_get('marketo_ma_webform_fields', array());
  $options = array();
  $disabled_options = array();
  foreach ($field_data as $field_key => $field_value) {
    $options[$field_value['id']] = array(
      'displayName' => $field_value['displayName'],
      'id' => $field_value['id'],
      'restName' => $field_value['restName'],
      'soapName' => $field_value['soapName'],
    );
    if ($field_value['restReadOnly'] || $field_value['soapReadOnly']) {
      $disabled_options[] = $field_value['id'];
    }
  }
  $form['marketo_ma_tabs']['marketo_ma_fields']['marketo_ma_webform_fields'] = array(
    '#type' => 'tableselect',
    '#header' => array(
      'displayName' => t('Display Name'),
      'id' => t('ID'),
      'restName' => t('REST Field'),
      'soapName' => t('SOAP/Munchkin Field'),
    ),
    '#options' => $options,
    '#default_value' => $field_defaults,
    '#title' => t('Marketo fields'),
    '#prefix' => '<div id="marketo-webform-fields-wrapper">',
    '#suffix' => '</div>',
  );
  foreach ($disabled_options as $field_key) {
    $form['marketo_ma_tabs']['marketo_ma_fields']['marketo_ma_webform_fields'][$field_key]['#disabled'] = TRUE;
  }

  $form['marketo_ma_tabs']['marketo_ma_fields']['marketo_ma_lead_fields_button'] = array(
    '#type' => 'button',
    '#value' => t('Retrieve from Marketo'),
    '#ajax' => array(
      'callback' => '_marketo_ma_lead_fields_button_callback',
      'wrapper' => 'marketo-webform-fields-wrapper',
    ),
  );
  // If API settings have not been configured, the button should be disabled.
  if (!_marketo_ma_rest_is_configured()) {
    $form['marketo_ma_tabs']['marketo_ma_fields']['marketo_ma_lead_fields_button']['#disabled'] = TRUE;
  }

  $form['marketo_ma_tabs']['marketo_ma_page_vis'] = array(
    '#title' => t('Page Visibility'),
    '#type' => 'fieldset',
  );
  $form['marketo_ma_tabs']['marketo_ma_page_vis']['marketo_ma_visibility_pages'] = array(
    '#title' => t('Add tracking to specific pages'),
    '#type' => 'radios',
    '#options' => array(
      t('All pages except those listed below'),
      t('Only the pages listed below'),
    ),
    '#default_value' => variable_get('marketo_ma_visibility_pages', 0),
    '#required' => TRUE,
  );
  $form['marketo_ma_tabs']['marketo_ma_page_vis']['marketo_ma_pages'] = array(
    '#type' => 'textarea',
    '#title' => t('Pages'),
    '#title_display' => 'invisible',
    '#default_value' => variable_get('marketo_ma_pages', MARKETO_MA_PAGES),
    '#description' => t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array(
      '%blog' => 'blog',
      '%blog-wildcard' => 'blog/*',
      '%front' => '<front>')
    ),
    '#rows' => 10,
  );

  $form['marketo_ma_tabs']['marketo_ma_role_vis'] = array(
    '#title' => t('Role Visibility'),
    '#type' => 'fieldset',
  );
  $form['marketo_ma_tabs']['marketo_ma_role_vis']['marketo_ma_visibility_roles'] = array(
    '#title' => t('Add tracking to specific roles'),
    '#type' => 'radios',
    '#options' => array(
      1 => t('All roles except those selected below'),
      0 => t('Only the roles selected below'),
    ),
    '#default_value' => variable_get('marketo_ma_visibility_roles', 1),
    '#required' => TRUE,
  );
  $role_options = array_map('check_plain', user_roles());
  $form['marketo_ma_tabs']['marketo_ma_role_vis']['marketo_ma_roles'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Roles'),
    '#default_value' => variable_get('marketo_ma_roles', array()),
    '#options' => $role_options,
  );

  // Add a submit handler.
  $form['#submit'][] = 'marketo_ma_webform_fields_submit';
  return system_settings_form($form);
}

function marketo_ma_webform_fields_submit($form, &$form_state) {
  $fields = array_filter($form_state['values']['marketo_ma_webform_fields']);
  $form_state['values']['marketo_ma_webform_fields'] = $fields;
}

/**
 * Implements hook_form_validate().
 */
function marketo_ma_admin_settings_form_validate($form, &$form_state) {
  // If using an API option attempt validation.
  if (
    $form_state['values']['marketo_ma_tracking_method'] == 'rest' ||
    $form_state['values']['marketo_ma_tracking_method'] == 'rest_async'
  ) {
    $form_state['values']['marketo_ma_rest_endpoint'] = trim($form_state['values']['marketo_ma_rest_endpoint']);
    $form_state['values']['marketo_ma_rest_identity'] = trim($form_state['values']['marketo_ma_rest_identity']);
    $form_state['values']['marketo_ma_rest_client_id'] = trim($form_state['values']['marketo_ma_rest_client_id']);
    $form_state['values']['marketo_ma_rest_client_secret'] = trim($form_state['values']['marketo_ma_rest_client_secret']);

    $proxy = array();
    $proxy['proxy_host'] = trim($form_state['values']['marketo_ma_rest_proxy_host']);
    $proxy['proxy_port'] = trim($form_state['values']['marketo_ma_rest_proxy_port']);
    $proxy['proxy_login'] = trim($form_state['values']['marketo_ma_rest_proxy_login']);
    $proxy['proxy_password'] = trim($form_state['values']['marketo_ma_rest_proxy_password']);

    if (empty($proxy['proxy_host'])) {
      unset($proxy['proxy_host']);
    }
    if (empty($proxy['proxy_port'])) {
      unset($proxy['proxy_port']);
    }
    if (empty($proxy['proxy_login'])) {
      unset($proxy['proxy_login']);
    }
    if (empty($proxy['proxy_password'])) {
      unset($proxy['proxy_password']);
    }

    $config_is_valid = _marketo_ma_validate_rest_configuration(
      $form_state['values']['marketo_ma_rest_endpoint'],
      $form_state['values']['marketo_ma_rest_identity'],
      $form_state['values']['marketo_ma_rest_client_id'],
      $form_state['values']['marketo_ma_rest_client_secret'],
      $proxy
    );
    if (!$config_is_valid) {
      form_set_error('marketo_ma_rest_endpoint', 'Unable to validate REST API settings.');
      form_set_error('marketo_ma_rest_identity');
      form_set_error('marketo_ma_rest_client_id');
      form_set_error('marketo_ma_rest_client_secret');
    }
  }
}

/**
 * Performs validation for Marketo REST API settings.
 * 
 * @param string endpoint
 *   REST Endpoint
 * @param string $identity
 *   REST Identity
 * @param string $client_id
 *   Client ID
 * @param string $client_secret
 *   Client Secret
 * @param array $proxy
 *   Proxy configuration options
 * 
 * @return bool
 *   Returns TRUE if REST settings are validated successfully
 */
function _marketo_ma_validate_rest_configuration($endpoint, $identity, $client_id, $client_secret, $proxy) {
  $result = FALSE;
  try {
    variable_del('marketo_ma_rest_token');
    $client = _marketo_ma_client($endpoint, $identity, $client_id, $client_secret);
    variable_set('marketo_ma_rest_token', $client->getAccessToken());
    $result = TRUE;
  }
  catch (Exception $e) {
    $result = FALSE;
  }
  return $result;
}

/**
 * Callback for updating webform fields.
 */
function _marketo_ma_lead_fields_button_callback($form, &$form_state) {
  return $form['marketo_ma_tabs']['marketo_ma_fields']['marketo_ma_webform_fields'];
}
