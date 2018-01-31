<?php

namespace Drupal\Tests\marketo_ma_webform\Functional;

use Drupal\webform\Entity\Webform;
use Drupal\webform\Element\WebformSelectOther;

/**
 * Marketo MA Webform Handler Form tests.
 *
 * @group marketo_ma_webform
 */
class MarketoMaWebformHandlerFormTest extends MarketoMaWebformHandlerFunctionalTestBase {

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = [];

  /**
   * Tests webform handler plugin.
   */
  public function testWebformHandlerAdmin() {
    // Setup assert session.
    $assert = $this->assertSession();
    // Login.
    $this->drupalLogin($this->rootUser);
    // Load test form.
    Webform::load('contact');
    // Add handler to web form.
    $this->drupalGet('admin/structure/webform/manage/contact/handlers/add/marketo_ma');
    $assert->pageTextContains('Sends a webform submission via Marketo MA.');
    $assert->pageTextContains('Webform to Marketo MA Lead mapping');
    $this->drupalPostForm('admin/structure/webform/manage/contact/handlers/add/marketo_ma', ['handler_id' => 'marketo_ma', 'status' => TRUE], t('Save'));
    $assert->pageTextContains('Sends a webform submission via Marketo MA.');

    // Map an enabled field using "other."
    // Using "other" to skip having to mock or load Marketo fields.
    $this->drupalPostForm('admin/structure/webform/manage/contact/handlers/marketo_ma/edit', ['settings[marketo_ma_mapping][email][select]' => WebformSelectOther::OTHER_OPTION, 'settings[marketo_ma_mapping][email][other]' => 'foo'], t('Save'));
    $this->drupalGet('admin/structure/webform/manage/contact/handlers/marketo_ma/edit');
    $assert->fieldValueEquals('settings[marketo_ma_mapping][email][select]', WebformSelectOther::OTHER_OPTION);
    $assert->fieldValueEquals('settings[marketo_ma_mapping][email][other]', 'foo');
  }

}
