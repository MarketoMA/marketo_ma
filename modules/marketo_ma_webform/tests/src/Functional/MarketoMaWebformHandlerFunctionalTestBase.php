<?php

namespace Drupal\Tests\marketo_ma_webform\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\webform\Tests\WebformTestTrait;

/**
 * Marketo MA Webform Handler Functional Test Base Class.
 *
 * @group marketo_ma_webform
 */
class MarketoMaWebformHandlerFunctionalTestBase extends BrowserTestBase {

  use WebformTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'webform',
    'marketo_ma_webform',
    'webform_test_handler',
  ];

}
