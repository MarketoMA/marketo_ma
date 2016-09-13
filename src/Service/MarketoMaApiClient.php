<?php

namespace Drupal\marketo_ma\Service;

use CSD\Marketo\Client;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\encryption\EncryptionTrait;
use Drupal\marketo_ma\Lead;

/**
 * This is a wrapper for the default API client library. It could be switched
 * out by another module that supplies an alternate API client library.
 */
class MarketoMaApiClient implements MarketoMaApiClientInterface {


  // Adds ability to encrypt/decrypt configuration.
  use EncryptionTrait;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The API client library. @see: https://github.com/dchesterton/marketo-rest-api.
   *
   * @var \CSD\Marketo\ClientInterface
   */
  private $client;

  /**
   * The config used to instantiate the REST client.
   *
   * @var array
   */
  private $client_config;

  /**
   * Creates the Marketo API client wrapper service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;

    $config = $this->config();

    // Build the config for the REST API Client.
    $this->client_config = [
      'client_id' => $this->decrypt($config->get('rest.client_id')),
      'client_secret' => $this->decrypt($config->get('rest.client_secret')),
      'munchkin_id' => $this->decrypt($config->get('munchkin.account_id')),
    ];
  }

  /**
   * Get's marketo_ma settings.
   * @return \Drupal\Core\Config\ImmutableConfig
   */
  protected function config() {
    return $this->configFactory->get('marketo_ma.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $fields_result = $this->getClient()->getFields()->getResult();

    array_walk($fields_result, function (&$field_item) {
      $field_item['default_name'] = $field_item['rest']['name'];
    });

    return $fields_result;
  }

  /**
   * {@inheritdoc}
   */
  public function getActivityTypes() {
    return $this->getClient()->getActivityTypes()->getResult();
  }

  /**
   * {@inheritdoc}
   */
  public function canConnect() {
    return !empty($this->client_config['munchkin_id'])
      && !empty($this->client_config['client_id'])
      && !empty($this->client_config['munchkin_id']);
  }

  /**
   * Instantiate the REST API client.
   */
  protected function getClient() {
    if (!isset($this->client)) {
      $this->client = Client::factory($this->client_config);
    }
    return $this->client;
  }

  /**
   * {@inheritdoc}
   */
  public function getLeadById($id) {
    $leads_result = $this->getClient()->getLead($id)->getLead();
    return !empty($leads_result) ? new Lead($leads_result) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLeadByEmail($email) {
    $leads_result = $this->getClient()->getLeadByFilterType('email', $email)->getResult();
    return !empty($leads_result[0]) ? new Lead(reset($leads_result)) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLeadActivity(Lead $lead, $activity_type_ids = []) {
    // @todo: split activity_type_ids into groups of 10 and concatenate results.
    // A paging token is required by the activities.json call.
    $paging_token = $this->getClient()->getPagingToken(date('c'))->getNextPageToken();
    // Calls get lead activities on the API client.
    return $this->getClient()->getLeadActivity($paging_token, $lead->id(), $activity_type_ids)->getLeadActivity();
  }

  /**
   * {@inheritdoc}
   */
  public function syncLead(Lead $lead, $key = 'email', $options = []) {
    // Add the create/update leads call to do the association.
    $result = $this->getClient()->createOrUpdateLeads([$lead->data()], $key, $options)->getResult();
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLead($leads, $args = array()) {
    return $this->getClient()->deleteLead($leads)->getResult();
  }

}
