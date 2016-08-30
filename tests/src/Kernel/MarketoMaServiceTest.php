<?php

namespace Drupal\Tests\marketo_ma\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Site\Settings;
use Drupal\marketo_ma\Lead;
use Drupal\marketo_ma\MarketoMaApiClientInterface;
use Drupal\marketo_ma\MarketoMaServiceInterface;

/**
 * @group marketo_ma
 */
class MarketoMaServiceTest extends KernelTestBase {

  /**
   * The marketo_ma service.
   *
   * @var \Drupal\marketo_ma\MarketoMaServiceInterface
   */
  protected $service;

  /**
   * The marketo_ma rest client service.
   *
   * @var MarketoMaApiClientInterface
   */
  protected $api_client;

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
    $config->set('tracking_method', 'api_client')
      ->set('munchkin.account_id', $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')))
      ->set('rest.client_id', $encryption_service->encrypt(getenv('marketo_ma_rest_client_id')))
      ->set('rest.client_secret', $encryption_service->encrypt(getenv('marketo_ma_rest_client_secret')))
      ->save();

    // Get the `marketo_ma` service.
    $this->service = \Drupal::service('marketo_ma');
    // Get the API client service.
    $this->api_client = \Drupal::service('marketo_ma.api_client');
  }

  /**
   * Tests the marketo_ma service.
   */
  public function testMarketoMaService() {
    self::assertTrue($this->service instanceof MarketoMaServiceInterface);
    self::assertEquals('api_client', $this->service->trackingMethod());
  }

  /**
   * Tests syncing leads in batch mode.
   */
  public function testBatchSync() {
    // Get the API settings.
    $config = \Drupal::configFactory()->getEditable('marketo_ma.settings');

    // Set up required settings.
    $config->set('rest.batch_requests', 1)
      ->save();

    // Queue up a lead.
    $user_email = $this->randomMachineName() . '@marketo.com';
    $this->service->updateLead(new Lead(['email' => $user_email]));

    // Try to load the new lead.
    $synced_lead = $this->api_client->getLeadByEmail($user_email);
    // Make sure the lead wasn't created.
    self::assertEmpty($synced_lead, 'The lead has not been created.');

    // Run cron, which should insert the user.
    \Drupal::service('cron')->run();

    // Get the queue.
    $lead_queue = \Drupal::queue('marketo_ma_lead');
    self::assertEquals($lead_queue->numberOfItems(), 0, 'The lead queue was emptied.');

    // Try to load the new lead.
    $synced_lead = $this->api_client->getLeadByEmail($user_email);
    // Make sure the lead was created.
    self::assertNotEmpty($synced_lead, 'The lead has not been created.');

    // Delete the newly created lead.
    $delete_result = $this->api_client->deleteLead($synced_lead->id());
    // Make sure an item was deleted.
    self::assertEquals('deleted', $delete_result[0]['status'], 'The tem lead was deleted.');

    // Try to load the new lead.
    $deleted_lead = $this->api_client->getLeadByEmail($user_email);
    // Make sure the lead wasn't created.
    self::assertEmpty($deleted_lead, 'The lead has not been created.');
  }

  // @todo: Add tests for service public methods.
}
