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
  $is_active = _marketo_ma_webform_is_active($node->nid);

  $form['marketo_ma_webform_is_active'] = array(
    '#title' => t('Capture Data'),
    '#type' => 'checkbox',
    '#description' => t('If checked, captured form information will be sent to Marketo'),
    '#default_value' => $is_active,
  );

  $form['components'] = array(
    '#title' => t('Webform field mapping'),
    '#tree' => TRUE,
    '#theme' => 'marketo_ma_webform_fieldmap',
  );

  $default_options = array(MARKETO_MA_WEBFORM_COMPONENT_NONE => '- None -');
  $marketo_options = _marketo_ma_get_field_options();
  $options = array_merge($default_options, $marketo_options);
  $fieldmap = _marketo_ma_webform_get_mapped_fields($node->nid);
  foreach ($node->webform['components'] as $cid => $component) {
    $form['components'][$cid] = array(
      '#title' => $component['name'] . ' (' . $component['form_key'] . ')',
      '#title_display' => 'invisible',
      '#type' => 'select',
      '#options' => $options,
    );
    if (array_key_exists($cid, $fieldmap)) {
      if (!array_key_exists($fieldmap[$cid], $options)) {
        $form['components'][$cid]['#options'] = array_merge(
          array($fieldmap[$cid] => "Undefined Field ($fieldmap[$cid])"), $options
        );
      }
      $form['components'][$cid]['#default_value'] = $fieldmap[$cid];
    }
  }

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#submit' => array('_marketo_ma_webform_save_webform'),
  );

  return $form;
}

/**
 * Inserts marketo webform settings into the database.
 */
function _marketo_ma_webform_save_webform($form, &$form_state) {
  $node = $form['#node'];
  $values = $form_state['values'];

  $nid = $node->nid;

  db_merge(MARKETO_MA_SCHEMA_WEBFORM)
    ->key(array('nid' => $nid))
    ->fields(array(
      MARKETO_MA_WEBFORM_FIELD_ACTIVE => $values['marketo_ma_webform_is_active'],
    ))
    ->execute();

  if (isset($form_state['values']['components'])) {
    foreach ($form_state['values']['components'] as $cid => $marketo) {
      db_merge(MARKETO_MA_SCHEMA_WEBFORM_COMPONENT)
        ->key(array(
          'nid' => $nid,
          'cid' => $cid,
        ))
        ->fields(array(
          MARKETO_MA_WEBFORM_COMPONENT_KEY => $marketo,
        ))
        ->execute();
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
