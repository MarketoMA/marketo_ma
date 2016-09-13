<?php

namespace Drupal\Tests\mma_user\Functional;


/**
 * Tests the mma_user controller.
 *
 * @group mma_user
 */
class MmaUserLeadDataControllerTest extends MmaUserTestBase {

  /**
   * Tests mma_user controller.
   */
  public function testMmaUserLeadDataController() {
    $account = $this->container->get('current_user');
  }

}
