<?php

namespace Drupal\Tests\marketo_ma_user\Functional;

/**
 * Tests the marketo_ma_user controller.
 *
 * @group marketo_ma_user
 */
class MarketoMaUserLeadDataControllerTest extends MarketoMaUserTestBase {

  /**
   * Tests marketo_ma_user controller.
   */
  public function testMarketoMaUserLeadDataController() {
    $account = $this->container->get('current_user');
  }

}
