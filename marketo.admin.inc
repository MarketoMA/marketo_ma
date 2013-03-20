<?php

/**
 * @file
 * Administrative page callbacks for the marketo module.
 */

/**
 * Builds and returns the Marketo settings form.
 */
function marketo_admin_settings() {
  module_load_include('inc', 'marketo', 'marketo');
  
  $form['#submit'][] = '_marketo_admin_settings_submit';
  $form['marketo_tracking_options'] = array(
    '#title' => t('Tracking Options'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => FALSE
  );
  $form['marketo_tracking_options']['marketo_tracking_method'] = array(
    '#title' => t('Tracking Method'),
    '#type' => 'radios',
    '#options' => array(
      'munchkin' => t('Munchking Javascript API'),
      'soap' => t('SOAP API')
    ),
    '#default_value' => variable_get('marketo_tracking_method', 'munchkin'),
    '#required' => TRUE
  );
  $form['marketo_tracking_options']['marketo_visibility_pages'] = array(
    '#title' => t('Add tracking to specific pages'),
    '#type' => 'radios',
    '#options' => array(
      t('Every page except the listed pages'),
      t('The listed pages only'),
    ),
    '#default_value' => variable_get('marketo_visibility_pages', 0),
    '#required' => TRUE
  );
  $form['marketo_tracking_options']['marketo_pages'] = array(
    '#type' => 'textarea',
    '#title' => t('Pages'),
    '#title_display' => 'invisible',
    '#default_value' => variable_get('marketo_pages', MARKETO_PAGES),
    '#description' => t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>')),
    '#rows' => 10,
  );
  $form['marketo_tracking_options']['marketo_webform_fields'] = array(
    '#type' => 'textarea',
    '#title' => t('Marketo fields to expose to webforms'),
    '#title_display' => 'invisible',
    '#default_value' => variable_get('marketo_webform_fields', MARKETO_WEBFORM_FIELD_DEFAULTS),
    '#description' => t('Tab delimited strings of [API Name] [Friendly Label]. Enter one field per line.'),
    '#rows' => 10,
  );
  $form['marketo_munchkin'] = array(
    '#title' => t('Munchkin Javascript API'),
    '#type' => 'fieldset',
    '#description' => t('Values can be found on the Munchkin Admin page at Admin > Integration > Munchkin'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE
  );
  $form['marketo_munchkin']['marketo_munchkin_account_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Munchkin Account ID'),
    '#default_value' => variable_get('marketo_munchkin_account_id', ''),
    '#required' => TRUE,
  );
  $form['marketo_munchkin']['marketo_munchkin_api_private_key'] = array(
    '#type' => 'textfield',
    '#title' => t('API Private Key'),
    '#default_value' => variable_get('marketo_munchkin_api_private_key', ''),
    '#required' => TRUE,
  );
  $form['marketo_munchkin']['marketo_munchkin_advanced'] = array(
    '#title' => t('Advanced Settings'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE
  );
  $form['marketo_munchkin']['marketo_munchkin_advanced']['marketo_munchkin_javascript_library'] = array(
    '#type' => 'textfield',
    '#title' => t('Munchkin Javascript Library'),
    '#default_value' => variable_get('marketo_munchkin_javascript_library', '//munchkin.marketo.net/munchkin.js'),
    '#required' => TRUE,
  );
  $form['marketo_munchkin']['marketo_munchkin_advanced']['marketo_munchkin_tracking_code_type'] = array(
    '#type' => 'radios',
    '#title' => t('Tracking Code Type'),
    '#options' => array(
      'async' => t('Asynchronous'),
      'simple' => t('Simple')
    ),
    '#default_value' => variable_get('marketo_munchkin_tracking_code_type', 'async'),
    '#description' => t('Asynchronous code is faster than Simple.'),
    '#required' => TRUE,
  );
  $form['marketo_munchkin']['marketo_munchkin_advanced']['marketo_munchkin_partition'] = array(
    '#type' => 'textfield',
    '#title' => t('Workspace (Partition)'),
    '#default_value' => variable_get('marketo_munchkin_partition', ''),
    '#required' => FALSE
  );
  $form['marketo_soap'] = array(
    '#title' => t('SOAP API'),
    '#type' => 'fieldset',
    '#description' => t('Values can be found on the SOAP API Admin page at Admin > Integration > SOAP API'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE
  );
  $form['marketo_soap']['marketo_soap_endpoint'] = array(
    '#type' => 'textfield',
    '#title' => t('SOAP endpoint'),
    '#default_value' => variable_get('marketo_soap_endpoint', ''),
    '#required' => TRUE,
  );
  $form['marketo_soap']['marketo_soap_user_id'] = array(
    '#type' => 'textfield',
    '#title' => t('User ID'),
    '#default_value' => variable_get('marketo_soap_user_id', ''),
    '#required' => TRUE,
  );
  $form['marketo_soap']['marketo_soap_encryption_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Encryption Key'),
    '#default_value' => variable_get('marketo_soap_encryption_key', ''),
    '#required' => TRUE,
  );
  return system_settings_form($form);
}

function _marketo_admin_settings_submit($form_id, $form_values){
  // nothing for now
}