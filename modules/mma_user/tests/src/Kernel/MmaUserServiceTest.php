<?php

namespace Drupal\Tests\mma_user\Kernel;

use Drupal\mma_user\Service\MarketoMaUserServiceInterface;

/**
 * @coversDefaultClass \Drupal\mma_user\Service\MarketoMaUserService
 * @group mma_user
 */
class MmaUserServiceTest extends MmaUserTestBase {

  public function testLeadController() {

    $service = \Drupal::service('marketo_ma.user');

    self::assertTrue($service instanceof MarketoMaUserServiceInterface, 'The Marketo MA User service exists and implements the proper interface.');

  }

}
