<?php

namespace Drupal\Tests\mma_contact\Functional;

use Drupal\contact\Entity\ContactForm;
use Drupal\Tests\BrowserTestBase;

abstract class MmaContactTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['contact', 'contact_storage', 'mma_contact', 'marketo_ma', 'mma_contact_test', 'user', 'contact_block'];

  /**
   * {@inheritdoc}
   */
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

    $account = $this->drupalCreateUser(['administer contact forms', 'administer marketo']);
    $this->drupalLogin($account);
  }

}
