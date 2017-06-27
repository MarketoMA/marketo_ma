<?php

namespace Drupal\Tests\marketo_ma_contact\Functional;

use Drupal\contact\Entity\ContactForm;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Drupal\Tests\BrowserTestBase;

abstract class MarketoMaContactTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['contact', 'contact_storage', 'marketo_ma_contact', 'marketo_ma', 'marketo_ma_contact_test', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Enable some mappable fields.
    \Drupal::configFactory()->getEditable(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME)
      ->set('field.enabled_fields', [1 => 1, 2 => 2, 3 => 3, 4 => 4])
      ->save();
    // @var \Drupal\contact\ContactFormInterface $contact_form
    $contact_form = ContactForm::create([
      'label' => 'test contact',
      'id' => 'test_contact',
      'recipients' => ['foo@example.com'],
      'contact_storage_preview' => FALSE,
    ]);

    $contact_form->setThirdPartySetting('marketo_ma_contact', 'mapping', [
      'name' => 'firstName',
      'mail' => 'email',
    ]);
    // Enable marketo integration for the form.
    $contact_form->setThirdPartySetting('marketo_ma_contact', 'enabled', 1);

    $contact_form->save();

    $account = $this->drupalCreateUser(['administer contact forms', 'administer marketo']);
    $this->drupalLogin($account);
  }

}
