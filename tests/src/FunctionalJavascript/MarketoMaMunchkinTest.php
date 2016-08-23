<?php

namespace Drupal\Tests\marketo_ma\FunctionalJavascript;

use Drupal\marketo_ma\MarketoMaMunchkinInterface;

/**
 * Tests the Marketo MA module in Munchkin mode.
 *
 * @group marketo_ma-js
 */
class MarketoMaMunchkinTest extends MmaJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

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

    // Write the encryption keto to settings.php
//    $this->writeSettings([
//      'config' => [
//        'marketo_ma.settings' => (object) [
//          'value' => [
//            'tracking_method' => 'munchkin',
//            'instance_host' => $encryption_service->encrypt(getenv('marketo_ma_instance_host')),
//            'munchkin.account_id' => $encryption_service->encrypt(getenv('marketo_ma_munchkin_account_id')),
//            'munchkin.api_private_key' => $encryption_service->encrypt(getenv('marketo_ma_munchkin_api_private_key')),
//          ],
//          'required' => TRUE,
//        ],
//      ],
//    ]);
  }

  /**
   * Tests if a lead is associated when the user logs in.
   */
  public function testMunchkinLeadAssociation() {
    $marketo_user = $this->drupalCreateUser();

    $this->drupalGet('<front>');
    $page = $this->getSession()->getPage();
    // Get the marketo cookie.
    $marketo_cookie = $this->getSession()->getCookie('_mkto_trk');
    // Get drupal settings.
    $drupal_settings = $this->getDrupalSettings();

    // Make sure there weren't any page errors.
    self::assertEmpty($page->find('css', 'div[role=alert]'));
    // The marketo track settings should be there.
    self::assertTrue($drupal_settings['marketo_ma']['track'], 'The marketo track flag has been set.');
    // But no actions.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions']), 'There are no marketo actions for this request.');
    // Make sure the marketo cookie is there.
    self::assertNotEmpty($marketo_cookie, 'The marketo cookie has been set.');

    // Log into drupal.
    $this->drupalLogin($marketo_user);
    // Get the marketo cookie.
    $marketo_cookie = $this->getSession()->getCookie('_mkto_trk');
    // Get drupal settings.
    $drupal_settings = $this->getDrupalSettings();

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // There should be an "associateLead" action.
    self::assertTrue(!empty($drupal_settings['marketo_ma']['actions'][0]['action']), 'A munchkin action exists');
    self::assertTrue($drupal_settings['marketo_ma']['actions'][0]['action'] === MarketoMaMunchkinInterface::ACTION_ASSOCIATE_LEAD, 'The first action will associate the lead');
    self::assertTrue(!empty($drupal_settings['marketo_ma']['actions'][0]['hash']), 'The munchkin hash exists.');
    self::assertTrue(!empty($drupal_settings['marketo_ma']['actions'][0]['data']['email']), 'The user email exists.');
    self::assertTrue($drupal_settings['marketo_ma']['actions'][0]['data']['email'] === $marketo_user->getEmail(), 'The user email exists.');
    // Make sure the marketo cookie is there.
    self::assertNotEmpty($marketo_cookie, 'The marketo cookie has been set.');

    // Get a random cache busting page.
    $this->drupalGet($this->randomMachineName());

    $drupal_settings = $this->getDrupalSettings();
    // Get the marketo cookie.
    $marketo_cookie = $this->getSession()->getCookie('_mkto_trk');

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // But no actions.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions']));
    // Make sure the marketo cookie is there.
    self::assertNotEmpty($marketo_cookie, 'The marketo cookie has been set.');

  }

}
