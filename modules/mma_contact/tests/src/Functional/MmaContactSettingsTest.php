<?php

namespace Drupal\Tests\mma_contact\Functional;

use Drupal\contact\Entity\ContactForm;

/**
 * Tests the mma_contact admin settings.
 *
 * @group mma_contact
 *
 * @see \Drupal\mma_contact_test\TestMarketoMaApiClient
 */
class MmaContactSettingsTest extends MmaContactTestBase {

  public function testMarketoContactAdminSettingsUI() {
    $contact_form_id = 'test_contact' . strtolower($this->randomMachineName());
    $edit = [
      'label' => 'test contact',
      'id' => $contact_form_id,
      'recipients' => 'foo@example.com',
      'contact_storage_preview' => FALSE,
    ];
    $this->drupalPostForm('admin/structure/contact/add', $edit, 'Save');
    $this->assertSession()->pageTextContains('has been added');

    $edit = [
      'enabled' => 1,
      'mapping[name][mapping]' => 'firstName',
      'mapping[mail][mapping]' => 'email',
    ];
    $this->drupalPostForm("admin/structure/contact/manage/{$contact_form_id}/marketo", $edit, 'Save');

    $contact_form = ContactForm::load($contact_form_id);
    $this->assertEquals([
      'name' => 'firstName',
      'mail' => 'email',
    ], $contact_form->getThirdPartySetting('mma_contact', 'mapping'));
  }

}
