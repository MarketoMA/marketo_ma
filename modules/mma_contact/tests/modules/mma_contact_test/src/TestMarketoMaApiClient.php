<?php

namespace Drupal\mma_contact_test;

use Drupal\marketo_ma\MarketoMaApiClientInterface;

/**
 * Stub implementation of an marketo api client.
 */
class TestMarketoMaApiClient implements MarketoMaApiClientInterface {

  /**
   * {@inheritdoc}
   */
  protected $syncedLeads = [];

  /**
   * {@inheritdoc}
   */
  public function canConnect() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $fields = [];
    $fields[] = [
      'id' => 1,
      'displayName' => 'First name',
      'default_name' => 'firstName',
      'dataType' => 'string',
      'length' => 255,
    ];
    $fields[] = [
      'id' => 2,
      'displayName' => 'Second name',
      'default_name' => 'secondName',
      'dataType' => 'string',
      'length' => 255,
    ];
    $fields[] = [
      'id' => 3,
      'displayName' => 'mail',
      'default_name' => 'email',
      'dataType' => 'string',
      'length' => 255,
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getLead($key, $type) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLeadActivity($key, $type) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function syncLead($lead, $key = 'email', $cookie = null, $options = []) {
    $this->syncedLeads[] = $lead;
    return [];
  }

  public function getSyncedLeads() {
    return $this->syncedLeads;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLead($leads, $args = array()) {
    return [];
  }

}
