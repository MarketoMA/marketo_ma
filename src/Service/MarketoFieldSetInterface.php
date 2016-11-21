<?php

namespace Drupal\marketo_ma\Service;

use Drupal\Core\State\StateInterface;

/**
 * Defines the interface for the Marketo field collection.
 *
 * @ingroup marketo_ma
 */
interface MarketoFieldSetInterface extends StateInterface {

  /**
   * Gets all field definitions.
   *
   * @return \Drupal\marketo_ma\MarketoFieldDefinition[]
   */
  public function getAll();


  /**
   * Deletes all field definitions.
   *
   * @return bool
   */
  public function deleteAll();
}
