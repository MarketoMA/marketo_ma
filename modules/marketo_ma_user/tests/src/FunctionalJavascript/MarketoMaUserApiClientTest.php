<?php

namespace Drupal\Tests\marketo_ma_user\FunctionalJavascript;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;

/**
 * Tests the Marketo MA module in API Client mode.
 *
 * @group marketo_ma_user-js
 */
class MarketoMaUserApiClientTest extends MarketoMaUserJavascriptTestBase {

  /**
   * @var \Drupal\marketo_ma\Service\MarketoMaApiClientInterface The marketo_ma client service.
   */
  protected $client;

  /**
   * @var \Drupal\marketo_ma\Service\MarketoMaServiceInterface The marketo_ma client service.
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Get the encryption service.
    $encryption_service = \Drupal::service('encryption');

    // Get the API settings.
    $config = \Drupal::configFactory()->getEditable(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME);

    // Set up required settings.
    $config->set('tracking_method', 'api_client');
    $config->set('munchkin.account_id', $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')));
    $config->set('rest.client_id', $encryption_service->encrypt(getenv('marketo_ma_rest_client_id')));
    $config->set('rest.client_secret', $encryption_service->encrypt(getenv('marketo_ma_rest_client_secret')));
    $config->save();

    // Get the API client service.
    $this->client = \Drupal::service('marketo_ma.api_client');
    // Get the API client service.
    $this->service = \Drupal::service('marketo_ma');

  }

  /**
   * Tests if a lead is associated when the user logs in.
   */
  public function testMunchkinLeadAssociation() {
    $marketo_user = $this->drupalCreateUser(['administer site configuration', 'access administration pages']);

    $this->drupalGet('<front>');
    $page = $this->getSession()->getPage();

    // Make sure there weren't any page errors.
    self::assertEmpty($page->find('css', 'div[role=alert]'));

    // Get drupal settings.
    $drupal_settings = $this->getDrupalSettings();

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // But no actions.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions']));

    // Log into drupal.
    $this->drupalLogin($marketo_user);
    // Get drupal settings.
    $drupal_settings = $this->getDrupalSettings();
    // Load the lead via the API.
    $lead = $this->client->getLeadByEmail($marketo_user->getEmail());

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // But no actions.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions']));
    // Make sure A lead result was returned.
    self::assertTrue(!empty($lead), 'A lead was associated');
    // Makes sure the lead email is the same as the user.
    self::assertEquals($lead->getEmail(), $marketo_user->getEmail(), 'Marketo client lead email matches.');
    // make sure we have a marketo lead ID for the lead.
    self::assertNotEmpty($lead->id(), 'Marketo client lead has a lead id.');

    // Clean up and delete the lead.
    $this->client->deleteLead([$lead->id()]);

    // Get a tracked page.
    $this->drupalGet('/user/password');
    // Get drupal settings.
    $drupal_settings = $this->getDrupalSettings();

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // But no actions.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions']));

    // Get an un-tracked page.
    $this->drupalGet('/admin/');
    $drupal_settings = $this->getDrupalSettings();

    // Make sure there weren't any page errors.
    self::assertEmpty($page->find('css', 'div[role=alert]'));
    // The marketo track settings should be there.
    self::assertFalse(isset($drupal_settings['marketo_ma']['track']), 'The marketo track flag is not present.');

  }

}
