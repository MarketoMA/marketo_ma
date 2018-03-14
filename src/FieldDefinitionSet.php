<?php

namespace Drupal\marketo_ma;

use Drupal\marketo_ma\MarketoFieldDefinition;

class FieldDefinitionSet {

  private $fieldset = [];
  private $readonly = [];

  public function __construct() {
    $this->load();
  }

  private function load() {
    $result = db_select('marketo_ma_lead_fields', 'f')
      ->fields('f')
      ->orderBy('displayName')
      ->execute();
    
    foreach($result->fetchAllAssoc('restName', \PDO::FETCH_ASSOC) as $record) {
      $this->fieldset[$record['restName']] = new MarketoFieldDefinition($record);
      if ($record['restReadOnly'] || $record['soapReadOnly']) {
        $this->readonly[] = $record['id'];
      }
    }
  }

  public function add($field) {
    $execute = db_merge('marketo_ma_lead_fields')
      ->key(['id' => $field['id']])
      ->fields([
        'displayName' => $field['displayName'],
        'dataType' => $field['dataType'],
        'length' => isset($field['length']) ? $field['length'] : NULL,
        'restName' => isset($field['rest']['name']) ? $field['rest']['name'] : NULL,
        'restReadOnly' => (isset($field['rest']['readOnly']) && $field['rest']['readOnly']) ? 1 : 0,
        'soapName' => isset($field['soap']['name']) ? $field['soap']['name'] : NULL,
        'soapReadOnly' => (isset($field['soap']['readOnly']) && $field['soap']['readOnly']) ? 1 : 0,
      ])
      ->execute();
  }

  public function getAll() {
    return $this->fieldset;
  }

  public function getAllTableselect() {
    $options = [];
    foreach ($this->fieldset as $field) {
      $options[$field->id()] = $field->toTableSelectOption();
    }
    return $options;
  }

  public function getReadOnly() {
    return $this->readonly;
  }

}
