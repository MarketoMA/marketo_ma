<?php

namespace Drupal\mma_contact_test;

use Drupal\Core\State\StateInterface;
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
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  public function __construct(StateInterface $state) {
    $this->state = $state;
    $this->syncedLeads = $this->state->get(static::class, []);
  }

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
    $this->state->set(static::class, $this->syncedLeads);

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
