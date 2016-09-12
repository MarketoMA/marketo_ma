<?php

namespace Drupal\Tests\marketo_ma\FunctionalJavascript;

/**
 * Tests the Marketo MA module in API Client mode.
 *
 * @group marketo_ma-js
 */
class MarketoMaApiClientTest extends MmaJavascriptTestBase {

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
    // Make sure there was no lead for this user.
    self::assertTrue(empty($lead), 'A lead was associated');

  }

}
