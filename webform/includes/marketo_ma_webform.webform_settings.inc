<?php

/**
 * @file
 * Form elements and menu callbacks for managing Marketo settings on a Webform.
 */

/**
 * Form builder for managing a webform configuration.
 */
function marketo_ma_webform_settings_form($form, &$form_state, $node) {
  $form['#node'] = $node;
  $is_active = 0;
  $tracking_method = 'default';

  $webform = _marketo_ma_webform_details($node->nid);
  if ($webform) {
    $is_active = $webform->is_active;
    if (isset($webform->options['tracking_method'])) {
      $tracking_method = $webform->options['tracking_method'];
    }
  }

  $form['marketo_ma_webform_is_active'] = array(
    '#title' => t('Capture Data'),
    '#type' => 'checkbox',
    '#description' => t('If checked, captured form information will be sent to Marketo'),
    '#default_value' => $is_active,
  );
  $form['marketo_ma_advanced'] = array(
    '#title' => t('Advanced Options'),
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => ($tracking_method == 'default'),
  );

  $methods = array('default' => t('Default (As defined in Marketo MA config)'));
  if (_marketo_ma_munchkin_is_configured()) {
    $methods['munchkin'] = t('Munchkin');
  }
  if (_marketo_ma_forms2_is_configured()) {
    $methods['forms2'] = t('Forms 2.0');
  }
  if (_marketo_ma_rest_is_configured()) {
    $methods['rest'] = t('REST API (Synchronous)');
    $methods['rest_async'] = t('REST API (Asynchronous)');
  }
  $form['marketo_ma_advanced']['marketo_ma_webform_capture_method'] = array(
    '#title' => t('Capture Method'),
    '#type' => 'select',
    '#description' => t('Use an alternate tracking method for this specific form.'),
    '#default_value' => $tracking_method,
    '#options' => $methods,
  );

  $formid = isset($webform->options['formid']) ? $webform->options['formid'] : '';
  $form['marketo_ma_advanced']['marketo_ma_forms2_formid'] = array(
    '#type' => 'textfield',
    '#title' => t('Form ID'),
    '#description' => t('Required when capture method is "Forms 2.0". Form ID is a number and can be found in the Marketo form embed code.'),
    '#default_value' => $formid,
    '#states' => array(
      'visible' => array(
        ':input[name="marketo_ma_webform_capture_method"]' => array('value' => 'forms2'),
      ),
    ),
  );

  $form['components'] = array(
    '#title' => t('Webform field mapping'),
    '#tree' => TRUE,
    '#theme' => 'marketo_ma_webform_fieldmap',
  );

  $options = array(MARKETO_MA_WEBFORM_COMPONENT_NONE => '- None -');
  $options += _marketo_ma_get_field_options();
  $fieldmap = _marketo_ma_webform_get_mapped_fields($node->nid);
  foreach ($node->webform['components'] as $cid => $component) {
    $form['components'][$cid] = array(
      '#title' => filter_xss($component['name'] . ' (' . $component['form_key'] . ')'),
      '#title_display' => 'invisible',
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => MARKETO_MA_WEBFORM_COMPONENT_NONE
    );
    if (array_key_exists($cid, $fieldmap)) {
      if (!array_key_exists($fieldmap[$cid], $options)) {
        $form['components'][$cid]['#options'] += array(
          $fieldmap[$cid] => "Undefined Field ($fieldmap[$cid])"
        );
        drupal_set_message(t('An undefined field [@field] is currently used and should be updated.', array('@field' => $fieldmap[$cid])), 'warning');
      }
      $form['components'][$cid]['#default_value'] = $fieldmap[$cid];
    }
  }

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#validate' => array('_marketo_ma_webform_validate_webform'),
    '#submit' => array('_marketo_ma_webform_save_webform'),
  );

  return $form;
}

/**
 * Validation function for form.
 */
function _marketo_ma_webform_validate_webform($form, &$form_state) {
  $values = $form_state['values'];
  if (
    $values['marketo_ma_webform_capture_method'] == 'forms2' &&
    ($values['marketo_ma_forms2_formid'] == '' || !is_numeric($values['marketo_ma_forms2_formid']))
  ) {
    form_set_error('marketo_ma_forms2_formid', t('Form ID must be set to a number when using the Forms 2.0 capture method.'));
  }
}

/**
 * Inserts marketo webform settings into the database.
 */
function _marketo_ma_webform_save_webform($form, &$form_state) {
  $node = $form['#node'];
  $values = $form_state['values'];

  $nid = $node->nid;

  $options = array();
  if ($values['marketo_ma_webform_capture_method'] != 'default') {
    $options['tracking_method'] = $values['marketo_ma_webform_capture_method'];

    if ($values['marketo_ma_webform_capture_method'] == 'forms2') {
      $options['formid'] = check_plain(trim($values['marketo_ma_forms2_formid']));
    }
  }

  db_merge(MARKETO_MA_SCHEMA_WEBFORM)
    ->key(array('nid' => $nid))
    ->fields(array(
      MARKETO_MA_WEBFORM_FIELD_ACTIVE => $values['marketo_ma_webform_is_active'],
      MARKETO_MA_WEBFORM_OPTIONS => serialize($options),
    ))
    ->execute();

  if (isset($form_state['values']['components'])) {
    foreach ($form_state['values']['components'] as $cid => $marketo_id) {
      // Ignore non numeric values.
      if (is_numeric($marketo_id) || $marketo_id == MARKETO_MA_WEBFORM_COMPONENT_NONE) {
        db_merge(MARKETO_MA_SCHEMA_WEBFORM_COMPONENT)
          ->key(array(
            'nid' => $nid,
            'cid' => $cid,
          ))
          ->fields(array(
            MARKETO_MA_LEAD_FIELD_ID => $marketo_id,
          ))
          ->execute();
      }
    }
  }

  drupal_set_message(t('The configuration options have been saved.'));
}

/**
 * Theme function for formatting user profile fieldmap form.
 * 
 * @param array $vars
 *   element: Form element
 */
function theme_marketo_ma_webform_fieldmap($vars) {
  $element = $vars['element'];

  $table = array(
    'header' => array(
      'webform' => t('Webform'),
      'marketo' => t('Marketo'),
    ),
    'rows' => array(),
    'empty' => t('No form components have been defined.'),
  );

  foreach (element_children($element) as $key) {
    $row = array();
    $row['data'] = array();
    $row['data'][] = $element[$key]['#title'];
    $row['data'][] = drupal_render($element[$key]);
    $table['rows'][] = $row;
  }

  return theme('table', $table);
}

/**
 * Generate a list of all webforms avaliable on this site.
 */
function theme_marketo_ma_webform_list($vars) {
  $nodes = $vars['nodes'];
  $table = array(
    'header' => array(
      t('Title'),
      t('Tracking Enabled'),
      t('Components Mapped'),
      t('Manage'),
    ),
    'rows' => array(),
    'empty' => t('There are currently no webforms on your site.'),
  );

  foreach ($nodes as $node) {
    $table['rows'][] = array(
      l($node->title, 'node/' . $node->nid),
      $node->is_active ? t('Yes') : t('No'),
      $node->components ? $node->components : 0,
      node_access('update', $node) ? l(t('Edit'), 'node/' . $node->nid . '/webform/marketo') : '',
    );
  }

  return theme('table', $table);
}
