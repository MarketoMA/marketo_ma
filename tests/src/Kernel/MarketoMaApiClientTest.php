<?php

namespace Drupal\Tests\marketo_ma\Kernel;

use Drupal\marketo_ma\Lead;
use Drupal\marketo_ma\Service\MarketoMaApiClientInterface;

/**
 * @group marketo_ma
 */
class MarketoMaApiClientTest extends MarketoMaKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set up required settings.
    $this->config
      ->set('tracking_method', 'api_client')
      ->save();
  }

  /**
   * Tests the marketo_ma service.
   */
  public function testMarketoMaService() {
    self::assertTrue($this->api_client instanceof MarketoMaApiClientInterface);
    self::assertTrue($this->api_client->canConnect());
  }


  /**
   * Tests the getFields call.
   */
  public function testGetFields() {
    $fields = $this->api_client->getFields();

    self::assertTrue(is_array($fields));
    self::assertNotEmpty($fields);
  }

  /**
   * Tests the creating and deleting a lead.
   */
  public function testCreateLead() {
    // Create a new lead.
    $result = $this->_sync_lead();

    self::assertEquals('created', $result[0]['status']);
    self::assertTrue(is_numeric($result[0]['id']));

    // Delete the newly created lead.
    $delete_result = $this->api_client->deleteLead($result[0]['id']);

    // Check that the status is deleted.
    self::assertEquals('deleted', $delete_result[0]['status']);
    // Double check the lead ids match up.
    self::assertEquals($result[0]['id'], $delete_result[0]['id']);
  }

  /**
   * Tests the retrieval of a lead by email address.
   */
  public function testGetLead() {
    // Create a new lead.
    $create_result = $this->_sync_lead();

    // Retrieve the new lead via the api call.
    $lead = $this->api_client->getLeadByEmail($this->test_lead_email);

    // We should have a numeric lead id,
    self::assertTrue(is_numeric($lead->id()));
    // Check the retrieved leads email against the test value.
    self::assertEquals($this->test_lead_email, $lead->getEmail());
    // check that the lead ids match up.
    self::assertEquals($create_result[0]['id'], $lead->id());
    // Clean up and delete the lead.

    // Get the lead by Marketo Lead ID.
    $lead_by_id = $this->api_client->getLeadById($lead->id());
    // Check the retrieved leads email against the test value.
    self::assertEquals($lead, $lead_by_id);

    $this->api_client->deleteLead($create_result[0]['id']);
  }


  /**
   * Tests the retrieval of a lead by email address.
   */
  public function testGetLeadActivity() {
    // Create a new lead.
    $create_result = $this->_sync_lead();

    // Get the newly synced lead.
    $lead = $this->api_client->getLeadByEmail($this->test_lead_email);
    // Get lead activity.
    $activity = $this->api_client->getLeadActivity($lead);

    // @todo: Add test for valy activity information.

    // Clean up and delete the lead.
    $this->api_client->deleteLead($create_result[0]['id']);
  }

  /**
   * @return array the syncLead result.
   */
  private function _sync_lead() {
    return $this->api_client->syncLead(new Lead([
      'email' => $this->test_lead_email,
      'firstName' => 'Lead 1',
      'postalCode' => '94105',
    ]));
  }

}
