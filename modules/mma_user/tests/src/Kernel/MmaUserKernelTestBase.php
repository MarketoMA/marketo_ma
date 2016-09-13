<?php

namespace Drupal\Tests\mma_user\Kernel;

use Drupal\Tests\marketo_ma\Kernel\MarketoMaKernelTestBase;

abstract class MmaUserKernelTestBase extends MarketoMaKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'mma_user',
    'marketo_ma',
    'user',
    'system',
  ];

  public function setUp() {
    parent::setUp();

    $this->installConfig('mma_user');
    $this->installEntitySchema('user');
  }

}
