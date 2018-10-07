<?php

namespace Drupal\Tests\marketo_ma_user\Kernel;

use Drupal\marketo_ma_user\Service\MarketoMaUserServiceInterface;

/**
 * @coversDefaultClass \Drupal\marketo_ma_user\Service\MarketoMaUserService
 * @group marketo_ma_user
 */
class MarketoMaUserServiceTest extends MarketoMaUserKernelTestBase {

  /**
   *
   */
  public function testLeadController() {

    $service = \Drupal::service('marketo_ma.user');

    self::assertTrue($service instanceof MarketoMaUserServiceInterface, 'The Marketo MA User service exists and implements the proper interface.');

  }

}
