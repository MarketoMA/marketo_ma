<?php

namespace Drupal\Tests\marketo_ma_user\Kernel;

use Drupal\Tests\marketo_ma\Kernel\MarketoMaKernelTestBase;

/**
 *
 */
abstract class MarketoMaUserKernelTestBase extends MarketoMaKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'marketo_ma_user',
    'marketo_ma',
    'user',
    'system',
  ];

  /**
   *
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig('marketo_ma_user');
    $this->installEntitySchema('user');
  }

}
