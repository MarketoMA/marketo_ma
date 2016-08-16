<?php

namespace Drupal\marketo_ma;

/**
 * Service interface for the `marketo_ma` worker service.
 *
 * @package Drupal\marketo_ma
 */
interface MarketoMaServiceInterface {


  /**
   * Gets the marketo_ma config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig|null
   */
  public function config();

  /**
   * Handles pre-processing page variables for the marketo_ma.module file.
   *
   * @param $variables
   * @return null
   */
  public function preProcessPage(&$variables);

  /**
   * Check configuration vs the current request to determine tracking requirement.
   *
   * @return bool
   *   Whether the current request should be tracked.
   */
  public function trackCurrentRequest();

}
