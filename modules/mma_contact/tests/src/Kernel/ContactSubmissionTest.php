<?php

namespace Drupal\Tests\mma_contact\Kernel;

use Drupal\contact\Entity\ContactForm;
use Drupal\contact\Entity\Message;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\mma_contact\Hooks\ContactMessageInsert
 * @group mma_contact
 */
class ContactSubmissionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['contact', 'contact_storage', 'marketo_ma', 'mma_contact', 'mma_contact_test', 'user'];

  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\contact\ContactFormInterface $contact_form */
    $contact_form = ContactForm::create([
      'label' => 'test contact',
      'id' => 'test_contact',
      'recipients' => ['foo@example.com'],
      'contact_storage_preview' => FALSE,
    ]);

    $contact_form->setThirdPartySetting('mma_contact', 'mapping', [
      'name' => 'firstName',
      'mail' => 'email',
    ]);

    $contact_form->save();

    $this->installEntitySchema('contact_message');
  }

  public function testContactSubmission() {
    $contact = Message::create([
      'contact_form' => 'test_contact',
      'name' => 'My name',
      'mail' => 'example@example.com'
    ]);
    $contact->save();

    // @todo Potentially one could use a mock instead.
    $synced_leads = \Drupal::service('marketo_ma.client')->getSyncedLeads();
    $this->assertEquals([[
      'firstName' => 'My name',
      'email' => 'example@example.com',
    ]], $synced_leads);
  }

}
