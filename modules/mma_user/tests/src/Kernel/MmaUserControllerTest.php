<?php

namespace Drupal\Tests\mma_user\Kernel;

use Drupal\mma_user\Controller\MmaUserLeadDataController;
use Drupal\user\Entity\User;

/**
 * @group mma_user
 */
class MmaUserControllerTest extends MmaUserTestBase {

  public function testLeadController() {

    // Get the controller.
    $controller = MmaUserLeadDataController::create($this->container);

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
