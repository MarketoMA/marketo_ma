<?php

namespace Drupal\Tests\marketo_ma_user\Kernel;

use Drupal\marketo_ma\Form\MarketoMASettings;
use Drupal\marketo_ma_user\Form\MarketoMaUserSettings;

/**
 * @group marketo_ma_user
 */
class MarketoMaUserSettingsFormTest extends MarketoMaUserKernelTestBase {

  public function testLeadSettingsForm() {

    $marketo_settings_form = \Drupal::formBuilder()->getForm(MarketoMASettings::class);
    $marketo_user_settings_form = \Drupal::formBuilder()->getForm(MarketoMaUserSettings::class);

    self::assertNotEmpty($marketo_settings_form['user_settings_tab']['group_activities']['enabled_activities']);

    // Tests the rendered form page.
    $content = $this->render($marketo_settings_form);

    self::assertContains('No activity types, try retrieving from marketo.', $content, 'The empty text is shown for activity types.');

  }

}
