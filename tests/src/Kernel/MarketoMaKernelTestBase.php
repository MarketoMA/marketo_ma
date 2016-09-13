<?php

namespace Drupal\Tests\marketo_ma\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Site\Settings;

/**
 * @group marketo_ma
 */
abstract class MarketoMaKernelTestBase extends KernelTestBase {

  /**
   * @var \Drupal\marketo_ma\Service\MarketoMaApiClientInterface The marketo_ma client service.
   */
  protected $api_client;

  protected $test_lead_email;

  /** @var  \Drupal\Core\Config\Config */
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

    // Install config for this module.
    $this->installConfig('marketo_ma');

    // Get the settings object.
    $settings = Settings::getAll();
    // Add a randomly generated encryption key.
    new Settings($settings + ['encryption_key' => base64_encode(random_bytes(32))]);

    // Get the encryption service.
    $encryption_service = \Drupal::service('encryption');

    // Get the API settings.
    $this->config = \Drupal::configFactory()->getEditable('marketo_ma.settings');

    // Set up required settings.
    $this->config
      ->set('munchkin.account_id', $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')))
      ->set('rest.client_id', $encryption_service->encrypt(getenv('marketo_ma_rest_client_id')))
      ->set('rest.client_secret', $encryption_service->encrypt(getenv('marketo_ma_rest_client_secret')))
      ->set('munchkin.account_id', $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')))
      ->set('munchkin.api_private_key', $encryption_service->encrypt(getenv('marketo_ma_munchkin_api_private_key')))
      ->save();

    // Get the API client service.
    $this->api_client = \Drupal::service('marketo_ma.api_client');

    // Set the test lead ID.
    $this->test_lead_email = 'test_lead-'.$this->randomMachineName().'@marketo.com';
  }

}
