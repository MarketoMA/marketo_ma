<?php

namespace Drupal\marketo_ma\Service;

use Drupal\marketo_ma\Lead;

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
   *   Returns true of false.
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
   * Gets the activity types fields that are available from marketo.
   *
   * @see: http://developers.marketo.com/documentation/rest/get-activity-types/
   *
   * @return array
   *   All of the activity types.
   */
  public function getActivityTypes();

  /**
   * Retrieves lead information.
   *
   * @param string $email
   *   The leads email address.
   *
   * @return \Drupal\marketo_ma\Lead
   *   The lead.
   */
  public function getLeadByEmail($email);

  /**
   * Retrieves lead information.
   *
   * @param string $id
   *   The leads marketo id.
   *
   * @return \Drupal\marketo_ma\Lead
   *   The lead.
   */
  public function getLeadById($id);

  /**
   * Retrieves lead activity information.
   *
   * @param \Drupal\marketo_ma\Lead
   *   The lead.
   * @param array
   *   Activity type ids to be viewed.
   */
  public function getLeadActivity(Lead $lead, $activity_type_ids = []);

  /**
   * Inserts or updates a lead.
   *
   * @param \Drupal\marketo_ma\Lead $lead
   *   The lead to be updated.
   * @param string $key
   *   Lead Key, typically email address.
   * @param array $options
   *   Array of additional options to configure lead syncing.
   *
   * @return array
   *   An array of lead ids and status messages.
   */
  public function syncLead(Lead $lead, $key = 'email', $options = []);

  /**
   * Delete one or more leads.
   *
   * @param int|array $leads
   *   Either a single lead ID or an array of lead IDs.
   * @param array $args
   *
   * @return array
   *   An array of response messages and ids (`$ret[n][status] === 'deleted'`).
   */
  public function deleteLead($leads, $args = []);

  /**
   * Adds an e-mail address to a given list.
   *
   * @param int $listId
   *   The ID of the target list. The List Id can be obtained from the URL of
   *   he list in the UI, where the URL will resemble
   *   https://app-***.marketo.com/#ST1001A1. In this URL, the id is 1001, it
   *   will always be between the first set of letters in the URL and the
   *   second set of letters.
   * @param string $email
   *   The email address of the user that needs to be added to the list.
   * @param array $options
   *   Array of additional options to configure lead syncing.
   *
   * @return array|null
   *   An array of response messages (errors) or NULL if the transaction was
   *   successful.
   */
  public function addLeadToListByEmail($listId, $email, array $options = []);

  /**
   * Adds a given set of leads to a target static list.
   *
   * @param string $listId
   *   The ID of the target list.
   * @param array $leads
   *   An array of \Drupal\marketo_ma\Lead objects.
   * @param array $options
   *   Array of additional options to configure lead syncing.
   *
   * @return array|null
   *   An array of response messages (errors) or NULL if the transaction was
   *   successful.
   */
  public function addLeadsToList($listId, array $leads, array $options = []);

}
