<?php

namespace Drupal\Tests\marketo_ma\Kernel;

use Drupal\marketo_ma\Lead;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;

/**
 * Tests the marketo_ma service.
 *
 * @group marketo_ma
 */
class MarketoMaServiceTest extends MarketoMaKernelTestBase {

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

    // Set up required settings.
    $this->config->set('tracking_method', 'api_client')
      ->save();
  }

  /**
   * Tests the marketo_ma service.
   */
  public function testMarketoMaService() {
    self::assertTrue($this->service instanceof MarketoMaServiceInterface);
    self::assertEquals(MarketoMaServiceInterface::TRACKING_METHOD_API, $this->service->trackingMethod());
  }

  /**
   * Tests syncing leads in batch mode.
   */
  public function testBatchSync() {
    // Get the API settings.
    $config = \Drupal::configFactory()->getEditable(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME);

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
