<?php

namespace Drupal\marketo_ma_contact_test;

use Drupal\Core\State\StateInterface;
use Drupal\marketo_ma\Lead;
use Drupal\marketo_ma\Service\MarketoMaApiClientInterface;

/**
 * Stub implementation of an marketo api client.
 */
class TestMarketoMaApiClient implements MarketoMaApiClientInterface  {

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
    return [
      [
        'id' => 1,
        'displayName' => 'First name',
        'dataType' => "string",
        'rest' => [
          'name' => 'firstName',
          'readOnly' => FALSE,
        ],
        'soap' => [
          'name' => 'firstname',
          'readOnly' => FALSE,
        ],
      ],
      [
        'id' => 2,
        'displayName' => 'Second name',
        'dataType' => 'string',
        'rest' => [
          'name' => 'secondName',
          'readOnly' => FALSE,
        ],
        'soap' => [
          'name' => 'secondname',
          'readOnly' => FALSE,
        ],
      ],
      [
        'id' => 3,
        'displayName' => 'mail',
        'dataType' => 'string',
        'rest' => [
          'name' => 'email',
          'readOnly' => FALSE,
        ],
        'soap' => [
          'name' => 'email',
          'readOnly' => FALSE,
        ],
      ],
      [
        'id' => 4,
        'displayName' => 'Field Test',
        'dataType' => 'string',
        'rest' => [
          'name' => 'fieldTest',
          'readOnly' => FALSE,
        ],
        'soap' => [
          'name' => 'field_test',
          'readOnly' => FALSE,
        ],
      ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getLeadById($id) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLeadByEmail($email) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLeadActivity(Lead $lead, $activity_type_ids = []) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function syncLead(Lead $lead, $key = 'email', $cookie = null, $options = []) {
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

  /**
   * {@inheritdoc}
   */
  public function getActivityTypes() {
    return [];
  }

}
