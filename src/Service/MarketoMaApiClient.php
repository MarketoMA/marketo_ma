<?php

namespace Drupal\marketo_ma\Service;

use CSD\Marketo\Client;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\encryption\EncryptionTrait;
use Drupal\marketo_ma\Lead;
use Psr\Log\LoggerInterface;

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
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Creates the Marketo API client wrapper service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    $this->configFactory = $config_factory;
    $this->logger = $logger;

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
    return $this->configFactory->get(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $fields_result = $this->getClient()->describeLeads()->getResult();

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
      $config = $this->client_config;
      // Validate config so we don't generate an invalid argument exception.
      if (!empty($config['client_id']) && (!empty($config['url']) || !empty($config['munchkin_id']))) {
        $this->client = Client::factory($config);
      } else {
        $this->logger->warning('MarketoMaApiClient::getClient called but rest-api-client is missing some configuration.', $config);
      }
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
    return $this->getClient()->getLeadActivity($paging_token, $lead->id(), $activity_type_ids)->getResult();
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

  /**
   * {@inheritdoc}
   */
  public function requestCampaign($campaignId, $leads, $tokens = [], $args = []) {
    $result = $this->getClient()->requestCampaign($campaignId, $leads, $tokens, $args)->getResult();

    return $result;
  }

}
