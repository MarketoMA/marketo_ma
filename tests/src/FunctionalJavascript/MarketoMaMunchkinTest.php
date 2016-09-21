<?php

namespace Drupal\Tests\marketo_ma\FunctionalJavascript;

/**
 * Tests the Marketo MA module in Munchkin mode.
 *
 * @group marketo_ma-js
 */
class MarketoMaMunchkinTest extends MarketoMaJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set up required settings.
    $this->config
      ->set('tracking_method', 'munchkin')
      ->save();
  }

  /**
   * Tests if a lead is associated when the user logs in.
   */
  public function testMunchkinLeadAssociation() {
    $marketo_user = $this->drupalCreateUser(['administer site configuration', 'access administration pages']);

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
    // There not should be an "associateLead" action.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions'][0]['action']), 'A munchkin action exists');
    // Make sure the marketo cookie is there.
    self::assertNotEmpty($marketo_cookie, 'The marketo cookie has been set.');

    // Get a tracked page.
    $this->drupalGet('/user/password');
    $drupal_settings = $this->getDrupalSettings();

    // Get the marketo cookie.
    $marketo_cookie = $this->getSession()->getCookie('_mkto_trk');

    // The marketo track settings should be there.
    self::assertTrue(!empty($drupal_settings['marketo_ma']));
    // But no actions.
    self::assertTrue(empty($drupal_settings['marketo_ma']['actions']));
    // Make sure the marketo cookie is there.
    self::assertNotEmpty($marketo_cookie, 'The marketo cookie has been set.');

    // Get an un-tracked page.
    $this->drupalGet('/admin/');
    $drupal_settings = $this->getDrupalSettings();

    // Make sure there weren't any page errors.
    self::assertEmpty($page->find('css', 'div[role=alert]'));
    // The marketo track settings should be there.
    self::assertFalse(isset($drupal_settings['marketo_ma']['track']), 'The marketo track flag is not present.');

  }

}
