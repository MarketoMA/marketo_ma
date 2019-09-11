<?php

namespace Drupal\marketo_ma\Service;
use Drupal\marketo_ma\Lead;

/**
 * Service interface for the `marketo_ma` worker service.
 *
 * @todo Should this service has a syncLoad method as well, which would allow us
 *   to send them in bulk via a queue? On top of that there might be more things
 *   we could put into it, like the alter hook, see marketo_ma.api.php.
 *
 * @package Drupal\marketo_ma
 */
interface MarketoMaServiceInterface {

  /**
   * Tracking method IDs.
   */
  const TRACKING_METHOD_API = 'api_client';
  const TRACKING_METHOD_MUNCHKIN = 'munchkin';

  /**
   * The Marketo MA config name.
   */
  const MARKETO_MA_CONFIG_NAME = 'marketo_ma.settings';

  /**
   * Gets the marketo_ma config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig|null
   */
  public function config();

  /**
   * Handles `hook_page_attachments` for the marketo_ma module.
   *
   * @param $page
   *
   * @return null
   */
  public function pageAttachments(&$page);

  /**
   * Check configuration vs the current request to determine tracking requirement.
   *
   * @return bool
   *   Whether the current request should be tracked.
   */
  public function shouldTrackCurrentRequest();

  /**
   * Gets the tracking method from settings.
   *
   * @return string
   *   The tracking method.
   */
  public function trackingMethod();

  /**
   * Adds a given lead to a given list.
   *
   * @param \Drupal\marketo_ma\Lead $lead
   *   The Lead object.
   * @param int $listId
   *   The ID of the list in Marketo.
   *
   * @return $this
   */
  public function addLeadToList(Lead $lead, $listId);

  /**
   * Adds a given e-mail address to a list.
   *
   * @param string $email
   *   The e-mail address of the lead.
   * @param int $listId
   *   The ID of the list in Marketo.
   *
   * @return $this
   */
  public function addLeadToListByEmail($email, $listId);
  /**
   * Sets temporary user data for this session.
   *
   * @param \Drupal\marketo_ma\Lead $lead
   *   The marketo lead.
   *
   * @return $this
   */
  public function setUserData($lead);

  /**
   * Gets temporary user data for the current session.
   *
   * @return array
   *   The temporary user data.
   */
  public function getUserData();

  /**
   * Resets (deletes) the temporary user data for the current session.
   *
   * @return $this
   */
  public function resetUserData();

  /**
   * Determines whether the current session contains any temporary user data.
   *
   * @return bool
   */
  public function hasUserData();

  /**
   * Updates lead information respecting batch settings.
   *
   * @param \Drupal\marketo_ma\Lead $lead
   *   The Lead object.
   *
   * @return $this
   */
  public function updateLead($lead);

  /**
   * Get's fields that are defined in marketo.
   *
   * @param bool $reset
   *   Whether to try to refresh the fields form the API client.
   *
   * @return \Drupal\marketo_ma\MarketoFieldDefinition[]
   *   All marketo fields fields keyed by the marketo field ID.
   */
  public function getMarketoFields();

  /**
   * Get's all marketo fields converted to table select options.
   *
   * @param bool $reset
   *   Whether to try to refresh the fields form the API client.
   *
   * @return \Drupal\marketo_ma\MarketoFieldDefinition[]
   *   All marketo fields keyed by the marketo field ID.
   */
  public function getMarketoFieldsAsTableSelectOptions();

  /**
   * Retrieves and caches lead fields from Marketo.
   */
  public function resetMarketoFields();

  /**
   * Gets fields that are read-only in Marketo.
   *
   * @return array
   *   All fields available for mapping keyed by marketo field ID.
   */
  public function getReadOnly();

  /**
   * Get's fields that have been enabled.
   *
   * @return \Drupal\marketo_ma\MarketoFieldDefinition[]
   *   All fields available for mapping keyed by marketo field ID.
   */
  public function getEnabledFields();

  /**
   * Determines if the API client is configured and available.
   *
   * @return bool
   *   Whether or not the API client can connect
   */
  public function apiClientCanConnect();

  /**
   * Get the result from last updateLead call.
   *
   * @return array|null
   *   An array of lead ids and status messages.
   */
  public function getUpdateLeadResult();

}
