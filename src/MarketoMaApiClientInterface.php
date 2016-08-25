<?php

namespace Drupal\marketo_ma;

/**
 * Service interface for the `marketo_ma` API client.
 *
 * @package Drupal\marketo_ma
 */
interface MarketoMaApiClientInterface {


  /**
   * Checks whether the client has all the information necessary to attempt a
   *  connection.
   *
   * @return bool
   */
  public function canConnect();

  /**
   * Gets the lead fields that are available for leads (AKA describe).
   *
   * @see: http://developers.marketo.com/documentation/rest/describe/
   *
   * @return array
   *   All of the fields available for leads.
   */
  public function getFields();

  /**
   * Retrieves lead information.
   *
   * @param string $key
   *   Lead Key, typically email address
   * @param string $type
   *   Lead key type, auto-detection attempted if not supplied
   * @return \Drupal\marketo_ma\LeadInterface
   *   The lead.
   */
  public function getLead($key, $type);

  /**
   * Retrieves lead activity information.
   *
   * @param string $key
   *   Lead Key, typically email address
   * @param string $type
   *   Lead key type, auto-detection attempted if not supplied
   */
  public function getLeadActivity($key, $type);

  /**
   * Inserts or updates a lead.
   *
   * @param \Drupal\marketo_ma\LeadInterface $lead
   *   The lead to be updated.
   * @param string $key
   *   Lead Key, typically email address
   * @param array $options
   *   Array of additional options to configure lead syncing
   *
   * @return array
   *   An array of lead ids and status messages.
   */
  public function syncLead($lead, $key = 'email', $options = []);

  /**
   * Delete one or more leads.
   *
   * @param int|array $leads
   *   Either a single lead ID or an array of lead IDs
   * @param array     $args
   *
   * @return array
   *   An array of response messages and ids (`$ret[n][status] === 'deleted'`).
   */
  public function deleteLead($leads, $args = array());

}
