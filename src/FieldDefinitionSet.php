<?php

namespace Drupal\marketo_ma;

use Drupal\Core\Database\Database;

/**
 *
 */
class FieldDefinitionSet {

  private $fieldset = [];
  private $readonly = [];
  private $writeable = [];

  /**
   *
   */
  public function __construct() {
    $this->load();
  }

  /**
   *
   */
  private function load() {
    $this->fieldset = Database::getConnection()->select('marketo_ma_lead_fields', 'f')
      ->fields('f')
      ->orderBy('displayName')
      ->execute()
      ->fetchAllAssoc('id', \PDO::FETCH_ASSOC);
    foreach ($this->fieldset as $field_key => $field_value) {
      if ($field_value['restReadOnly'] || $field_value['soapReadOnly']) {
        $this->readonly[] = $field_value['id'];
      }
      else {
        $this->writeable[$field_key] = $field_value;
      }
    }
  }

  /**
   *
   */
  public function add($field) {
    Database::getConnection()->merge('marketo_ma_lead_fields')
      ->key('id', $field['id'])
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

  /**
   *
   */
  public function getAll() {
    return $this->fieldset;
  }

  /**
   *
   */
  public function getAllTableselect() {
    $options = [];
    foreach ($this->fieldset as $field_key => $field_value) {
      $options[$field_value['id']] = [
        'displayName' => $field_value['displayName'],
        'id' => $field_value['id'],
        'restName' => (string) $field_value['restName'],
        'soapName' => (string) $field_value['soapName'],
      ];
    }
    return $options;
  }

  /**
   *
   */
  public function getReadOnly() {
    return $this->readonly;
  }

  public function getWriteable() {
    return $this->writeable;
  }

}
