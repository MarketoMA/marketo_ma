<?php

namespace Drupal\marketo_ma\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\marketo_ma\MarketoMaApiClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Updates marketo lead.
 *
 * @QueueWorker(
 *   id = "marketo_ma_lead",
 *   title = @Translation("Marketo MA Lead"),
 *   cron = {"time" = 60}
 * )
 */
class MarketoMaLead extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The Marketo MA API Client.
   *
   * @var \Drupal\marketo_ma\MarketoMaApiClientInterface
   */
  protected $api_client;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\marketo_ma\MarketoMaApiClientInterface $api_client
   *   The marketo API client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MarketoMaApiClientInterface $api_client = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->api_client = $api_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('marketo_ma.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($lead) {
    // Use the API service to sync the lead.
    $this->api_client->syncLead($lead);
  }

}
