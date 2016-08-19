<?php

namespace Drupal\Tests\mma_contact\Functional;

use Drupal\contact\Entity\ContactForm;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the mma_contact admin settings.
 *
 * @group mma_contact
 *
 * @see \Drupal\mma_contact_test\TestMarketoMaApiClient
 */
class MmaContactSettingsTest extends MmaContactTestBase {

  public function testMarketoContactAdminSettingsUI() {
    $edit = [
      'label' => 'test contact',
      'id' => 'test_contact',
      'recipients' => 'foo@example.com',
      'contact_storage_preview' => FALSE,
    ];
    $this->drupalPostForm('admin/structure/contact/add', $edit, 'Save');
    $this->assertSession()->pageTextContains('has been added');

    $edit = [
      'enabled' => TRUE,
      'mapping[name][mapping]' => 'firstName',
      'mapping[mail][mapping]' => 'email',
    ];
    $this->drupalPostForm('admin/structure/contact/manage/test_contact/marketo', $edit, 'Save');

    $contact_form = ContactForm::load('test_contact');
    $this->assertEquals([
      'name' => 'firstName',
      'mail' => 'email',
    ], $contact_form->getThirdPartySetting('mma_contact', 'mapping'));
  }

}
