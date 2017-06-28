<?php

namespace Drupal\Tests\marketo_ma\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;

/**
 * Base for Marketo MA functional javascript tests.
 *
 * @group marketo_ma-js
 */
abstract class MarketoMaJavascriptTestBase extends JavascriptTestBase {

  /**
   * The marketo_ma api client.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaApiClientInterface
   */
  protected $client;

  /**
   * The marketo_ma service.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   */
  protected $service;

  /**
   * The marketo_ma config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'encryption',
    'marketo_ma',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Generate a random encryption key.
    $settings['settings']['encryption_key'] = (object) [
      'value' => base64_encode(random_bytes(32)),
      'required' => TRUE,
    ];
    // Write the encryption keto to settings.php.
    $this->writeSettings($settings);

    // Get the encryption service.
    $encryption_service = \Drupal::service('encryption');

    // Get the API settings.
    $this->config = \Drupal::configFactory()->getEditable(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME);

    // Set up required settings.
    $this->config
      ->set('instance_host', $encryption_service->encrypt(getenv('marketo_ma_instance_host')))
      ->set('munchkin.account_id', $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')))
      ->set('munchkin.api_private_key', $encryption_service->encrypt(getenv('marketo_ma_munchkin_api_private_key')))
      ->set('rest.client_id', $encryption_service->encrypt(getenv('marketo_ma_rest_client_id')))
      ->set('rest.client_secret', $encryption_service->encrypt(getenv('marketo_ma_rest_client_secret')))
      ->save();

    // Get the API client service.
    $this->client = \Drupal::service('marketo_ma.api_client');
    // Get the API client service.
    $this->service = \Drupal::service('marketo_ma');
  }

  /**
   * Gets drupal settings and parses into an php array.
   *
   * @return array
   *   The drupal settings object.
   */
  public function getDrupalSettings() {
    // Get the active drupal settings from the session.
    return $this->getSession()->evaluateScript('function(){ if (drupalSettings !== \'undefined\') { return drupalSettings; }}()');
  }

}
