<?php

namespace Drupal\marketo_ma;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;

/**
 * Represents a Marketo Field.
 *
 * @package Drupal\marketo_ma
 */
class MarketoFieldDefinition {

  use StringTranslationTrait;

  /**
   * The lead metadata.
   *
   * @var array
   */
  protected $definition;

  /**
   * MarketoField constructor.
   *
   * @param array $definition
   *   The Markeo field data.
   * @param mixed $value
   *   The Markeo instance value.
   */
  public function __construct($definition = [], $value = NULL) {
    $this->definition = $definition;
    $this->value = $value;
  }

  /**
   * Get the Marketo Field ID.
   *
   * @return string
   *   The Field ID.
   */
  public function id() {
    return $this->definition['id'];
  }

  /**
   * Gets the display name for the Marketo MA field.
   *
   * @return string
   *   The display name.
   */
  public function getDisplayName() {
    return $this->definition['displayName'];
  }

  /**
   * Get the field name for a given api type.
   *
   * @param string $tracking_method
   *   The source api for which the field name is being requested.
   * @return string
   *   The field name.
   */
  public function getFieldName($tracking_method) {
    $name_key = $tracking_method === MarketoMaServiceInterface::TRACKING_METHOD_MUNCHKIN ? 'soap' : 'rest';
    return isset($this->definition[$name_key]['name']) ? $this->definition[$name_key]['name'] : NULL;
  }

  /**
   * Gets the field as a tableselect option.
   *
   * @return array
   *   A tableselect ready array of values (id, name, rest_name, soap_name).
   */
  public function toTableSelectOption() {
    return [
      $this->t(':value', [':value' => $this->id()]),
      $this->t(':value', [':value' => $this->getDisplayName()]),
      $this->t(':value', [':value' => !isset($this->definition['rest']['name']) ? '' : $this->definition['rest']['name']]),
      $this->t(':value', [':value' => !isset($this->definition['soap']['name']) ? '' : $this->definition['soap']['name']]),
    ];
  }

}
