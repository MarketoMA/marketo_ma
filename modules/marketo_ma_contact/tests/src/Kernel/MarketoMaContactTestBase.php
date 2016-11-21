<?php

namespace Drupal\Tests\marketo_ma_contact\Kernel;

use Drupal\contact\Entity\ContactForm;
use Drupal\KernelTests\KernelTestBase;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;

abstract class MarketoMaContactTestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['contact', 'contact_storage', 'marketo_ma', 'marketo_ma_contact', 'marketo_ma_contact_test', 'user'];

  protected function setUp() {
    parent::setUp();

    $this->installConfig('marketo_ma');
    // Enable some mappable fields.
    \Drupal::configFactory()->getEditable(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME)
      ->set('field.enabled_fields', [1=>1, 2=>2, 3=>3])
      ->save();

    /** @var \Drupal\contact\ContactFormInterface $contact_form */
    $contact_form = ContactForm::create([
      'label' => 'test contact',
      'id' => 'test_contact',
      'recipients' => ['foo@example.com'],
      'contact_storage_preview' => FALSE,
    ]);

    $contact_form->setThirdPartySetting('marketo_ma_contact', 'mapping', [
      'name' => '1',
      'mail' => '3',
    ]);
    $contact_form->setThirdPartySetting('marketo_ma_contact', 'enabled', 1);

    $contact_form->save();

    $this->installEntitySchema('contact_message');
  }

}
