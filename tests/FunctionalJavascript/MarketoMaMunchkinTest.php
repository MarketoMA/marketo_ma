<?php

namespace Drupal\Tests\munchkin_ma\FunctionalJavascript;

use Drupal\Component\Serialization\Json;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\marketo_ma\MarketoMaMunchkinInterface;

/**
 * Tests the Marketo MA module in Munchkin mode.
 *
 * @group marketo_ma-js
 */
class MarketoMaMunchkinTest extends JavascriptTestBase {

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
    $settings['settings']['encryption_key'] = (object) array(
      'value' => base64_encode(random_bytes(32)),
      'required' => TRUE,
    );
    // Write the encryption keto to settings.php
    $this->writeSettings($settings);

    // Get the encryption service.
    $encryption_service = \Drupal::service('encryption');

    // Get the API settings.
    $config = \Drupal::configFactory()->getEditable('marketo_ma.settings');

    // Set up required settings.
    $config->set('tracking_method', 'munchkin');
    $config->set('instance_host', $encryption_service->encrypt(getenv('marketo_ma_instance_host')));
    $config->set('munchkin.account_id', $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')));
    $config->set('munchkin.api_private_key', $encryption_service->encrypt(getenv('marketo_ma_munchkin_api_private_key')));
    $config->save();

  }

  /**
   * Tests if a lead is associated when the user logs in.
   */
  public function testMunchkinLeadAssociation() {
    $marketo_user = $this->drupalCreateUser();

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

    $drupal_settings = $this->getDrupalSettings();

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // There should be an "associateLead" action.
    self::assertTrue(!empty($drupal_settings['marketo_ma']['actions'][0]['action']), 'A munchkin action exists');
    self::assertTrue($drupal_settings['marketo_ma']['actions'][0]['action'] === MarketoMaMunchkinInterface::ACTION_ASSOCIATE_LEAD, 'The first action will associate the lead');
    self::assertTrue(!empty($drupal_settings['marketo_ma']['actions'][0]['hash']), 'The munchkin hash exists.');
    self::assertTrue(!empty($drupal_settings['marketo_ma']['actions'][0]['data']['email']), 'The user email exists.');
    self::assertTrue($drupal_settings['marketo_ma']['actions'][0]['data']['email'] === $marketo_user->getEmail(), 'The user email exists.');

    // Go back to the front page.
    $this->drupalGet('<front>');
    $drupal_settings = $this->getDrupalSettings();

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // But no actions.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions']));

  }

  /**
   * Gets drupal settings and parses into an php array.
   *
   * @return array
   *   The drupal settings object.
   */
  public function getDrupalSettings() {
    // Get the settings html tag.
    $settings_tag = $this->getSession()->getPage()->find('css', 'script[data-drupal-selector=drupal-settings-json]');
    // Decode the innerHtml of the settings tag.
    return !empty($settings_tag) ? Json::decode($settings_tag->getHtml()) : [];
  }

}
