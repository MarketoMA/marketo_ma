<?php

namespace Drupal\Tests\marketo_ma\FunctionalJavascript;


use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Base for Marketo MA functional javascript tests.
 *
 * @group marketo_ma-js
 */
abstract class MmaJavascriptTestBase extends JavascriptTestBase {

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

  }

  /**
   * Gets drupal settings and parses into an php array.
   *
   * @return array
   *   The drupal settings object.
   */
  public function getDrupalSettings() {
    // Get the active drupal settings from the session.
    return $this->getSession()->evaluateScript('function(){ if (drupalSettings !== \'undefined\') { return drupalSettings; }}()');
  }

}
