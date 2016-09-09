<?php

namespace Drupal\Tests\mma_user\Functional;

use Drupal\Tests\BrowserTestBase;

abstract class MmaUserTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['user', 'marketo_ma', 'mma_user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $account = $this->drupalCreateUser(['access all marketo lead data', 'administer marketo']);
    $this->drupalLogin($account);
  }

}
