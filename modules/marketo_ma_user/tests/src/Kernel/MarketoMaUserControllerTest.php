<?php

namespace Drupal\Tests\marketo_ma_user\Kernel;

use Drupal\marketo_ma_user\Controller\MarketoMaUserLeadDataController;
use Drupal\user\Entity\User;

/**
 * @group marketo_ma_user
 */
class MarketoMaUserControllerKernelTest extends MarketoMaUserKernelTestBase {

  public function testLeadController() {

    // @todo: Add tests for the two controller callbacks.
    // Get the controller.
    $controller = MarketoMaUserLeadDataController::create($this->container);

    $account_name = $this->randomMachineName();
    $account_email = $this->randomMachineName() . '@example.com';
    // Render the lead page.
    $user = User::create([
      'uid' => 2,
      'email' => $account_email,
      'name' => $account_name,
    ]);

    // Get the page title.
    $title = (string) $controller->viewLeadTitle($user);
    // Build the output.
    $build = $controller->viewLead($user);
    // Render the printed output.
    $content = $this->render($build);

    self::assertSame("Marketo Lead ({$account_name})", $title, 'The title contains the user name.');

    self::assertNotEmpty($content, 'There is content.');

  }

}
