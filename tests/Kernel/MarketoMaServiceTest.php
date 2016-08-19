<?php

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Site\Settings;
use Drupal\marketo_ma\MarketoMaServiceInterface;

/**
 * @group marketo_ma
 */
class MarketoMaServiceTest extends KernelTestBase {

  /**
   * @var \Drupal\marketo_ma\MarketoMaServiceInterface The marketo_ma client service.
   */
  protected $service;

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

    // Install config for this module.
    $this->installConfig('marketo_ma');

    // Get the settings object.
    $settings = Settings::getAll();
    // Add a randomly generated encryption key.
    new Settings($settings + ['encryption_key' => base64_encode(random_bytes(32))]);

    // Get the encryption service.
    $encryption_service = \Drupal::service('encryption');

    // Get the API settings.
    $config = \Drupal::configFactory()->getEditable('marketo_ma.settings');

    // Set up required settings.
    $config->set('tracking_method', 'api_client');
    $config->set('munchkin.account_id', $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')));
    $config->set('rest.client_id', $encryption_service->encrypt(getenv('marketo_ma_rest_client_id')));
    $config->set('rest.client_secret', $encryption_service->encrypt(getenv('marketo_ma_rest_client_secret')));
    $config->save();

    // Get the API client service.
    $this->service = \Drupal::service('marketo_ma');
  }

  /**
   * Tests the marketo_ma service.
   */
  public function testMarketoMaService() {
    self::assertTrue($this->service instanceof MarketoMaServiceInterface);
    self::assertEquals('api_client', $this->service->trackingMethod());
  }

  // @todo: Add tests for service public methods.
}
