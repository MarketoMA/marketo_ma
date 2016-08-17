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
   * Determines if all of the configuration is complete for munchkin integration.
   *
   * @return bool
   */
  public function isConfigured();

  /**
   * Gets the Munchkin account ID.
   *
   * @return string
   */
  public function getAccountID();

  /**
   * Gets the Munchkin account ID.
   *
   * @return string
   */
  public function getLibrary();
}
