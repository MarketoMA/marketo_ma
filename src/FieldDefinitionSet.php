<?php

namespace Drupal\marketo_ma;

class FieldDefinitionSet {

  private $fieldset = array();

  public function __construct() {
    $this->load();
  }

  private function load() {
    $this->fieldset = db_select('marketo_ma_lead_fields', 'f')
      ->fields('f')
      ->orderBy('displayName')
      ->execute()
      ->fetchAllAssoc('restName', \PDO::FETCH_ASSOC);
  }

  public function add($field) {
    $execute = db_merge('marketo_ma_lead_fields')
      ->key(array('id' => $field['id']))
      ->fields(array(
        'displayName' => $field['displayName'],
        'dataType' => $field['dataType'],
        'length' => isset($field['length']) ? $field['length'] : NULL,
        'restName' => isset($field['rest']['name']) ? $field['rest']['name'] : NULL,
        'restReadOnly' => (isset($field['rest']['readOnly']) && $field['rest']['readOnly']) ? 1 : 0,
        'soapName' => isset($field['soap']['name']) ? $field['soap']['name'] : NULL,
        'soapReadOnly' => (isset($field['soap']['readOnly']) && $field['soap']['readOnly']) ? 1 : 0,
      ))
      ->execute();
  }

  public function get() {
    
  }

  public function getAll() {
    return $this->fieldset;
  }

  public function getAllTableselect() {
    $this->load();
    $options = array();
    $disabled_options = array();
    foreach ($this->fieldset as $field_key => $field_value) {
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
    return $options;
  }

}
