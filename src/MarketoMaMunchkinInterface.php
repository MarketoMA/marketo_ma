<?php

namespace Drupal\marketo_ma;

/**
 * Service interface for the `marketo_ma` munchkin service (marketo_ma.munchkin).
 *
 * @package Drupal\marketo_ma
 */
interface MarketoMaMunchkinInterface {

  /**
   * Gets the marketo_ma config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig|null
   */
  public function config();

  /**
   * @return bool
   *   Returns true if all dependent munchkin configuration is set.
   */
  public function isConfigured();

}
