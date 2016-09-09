<?php

namespace Drupal\Tests\mma_user\Kernel;

use Drupal\KernelTests\KernelTestBase;

abstract class MmaUserTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['mma_user', 'marketo_ma', 'user', 'system'];

  protected function setUp() {
    parent::setUp();

    $this->installConfig('marketo_ma');
    $this->installConfig('mma_user');
    $this->installEntitySchema('user');
  }

}
