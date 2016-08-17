<?php

use Drupal\KernelTests\KernelTestBase;
use \Drupal\marketo_ma\MarketoMaApiClientInterface;
use Drupal\Core\Site\Settings;

/**
 * @group marketo_ma
 */
class MarketoMaApiClientTest extends KernelTestBase {

  /**
   * @var \Drupal\marketo_ma\MarketoMaApiClientInterface The marketo_ma client service.
   */
  protected $client;

  protected $test_lead_id;
  /**
   * {@inheritdoc}
   */
  public static $modules = [
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
    $this->client = \Drupal::service('marketo_ma.client');

    // Set the test lead ID.
    $this->test_lead_id = 'test_lead-'.$this->randomMachineName().'@marketo.com';
  }


  /**
   * Tests the marketo_ma service.
   */
  public function testMarketoMaService() {
    self::assertTrue($this->client instanceof MarketoMaApiClientInterface);
    self::assertTrue($this->client->canConnect());
  }


  /**
   * Tests the getFields call.
   */
  public function testGetFields() {
    $fields = $this->client->getFields();

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
    $delete_result = $this->client->deleteLead($result[0]['id']);

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
    $lead = $this->client->getLead('email', $this->test_lead_id);

    // We should have a numeric lead id,
    self::assertTrue(is_numeric($lead[0]['id']));
    // Check the retrieved leads email against the test value.
    self::assertEquals($this->test_lead_id, $lead[0]['email']);
    // check that the lead ids match up.
    self::assertEquals($create_result[0]['id'], $lead[0]['id']);
    // Clean up and delete the lead.
    $this->client->deleteLead($create_result[0]['id']);
  }


  /**
   * Tests the retrieval of a lead by email address.
   */
  public function testGetLeadActivity() {
    // Create a new lead.
    $create_result = $this->_sync_lead();

    // Get lead activity.
    $activity = $this->client->getLeadActivity($this->test_lead_id, 'email');

    // @todo: Add test for valy activity information.

    // Clean up and delete the lead.
    $this->client->deleteLead($create_result[0]['id']);
  }

  /**
   * @return array the syncLead result.
   */
  private function _sync_lead() {
    return $this->client->syncLead([
      'email' => $this->test_lead_id,
      'firstName' => 'Lead 1',
      'postalCode' => '94105',
    ]);
  }

}
