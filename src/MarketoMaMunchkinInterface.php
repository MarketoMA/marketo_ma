<?php

namespace Drupal\marketo_ma;

/**
 * Service interface for the `marketo_ma` munchkin service (marketo_ma.munchkin).
 *
 * @package Drupal\marketo_ma
 */
interface MarketoMaMunchkinInterface {

  /**
   * Constants for munchkin javascript actions.
   */
  const ACTION_VISIT_PAGE = 'visitWebPage';
  const ACTION_CLICK_LINK = 'clickLink';
  const ACTION_ASSOCIATE_LEAD = 'associateLead';

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

  /**
   * Get a munchkin action given the action type and lead information.
   * @see: http://developers.marketo.com/documentation/websites/munchkin-api/
   *
   * @param $action_type
   *   The type of action to be preformed. ('visitWebPage', 'clickLink', 'associateLead')
   * @param LeadInterface $lead
   *   The lead to be associated. Note: A Lead email is required the
   *   `associateLead` action.
   * @param array $args
   *   Required args for 'visitWebPage' or 'clickLink' actions.
   *
   * @return array
   *   The Drupal settings array required for the action.
   */
  public function getAction($action_type, LeadInterface $lead, $args = []);

}
