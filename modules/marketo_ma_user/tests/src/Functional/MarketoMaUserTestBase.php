<?php

namespace Drupal\Tests\marketo_ma_user\Functional;

use Drupal\Tests\BrowserTestBase;

abstract class MarketoMaUserTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['user', 'marketo_ma', 'marketo_ma_user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $account = $this->drupalCreateUser(['access all marketo lead data', 'administer marketo']);
    $this->drupalLogin($account);
  }

}
