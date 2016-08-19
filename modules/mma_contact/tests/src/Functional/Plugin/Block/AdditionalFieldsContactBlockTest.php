<?php

namespace Drupal\Tests\mma_contact\Kernel\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityFormMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\mma_contact_test\TestMarketoMaApiClient;
use Drupal\simpletest\BlockCreationTrait;
use Drupal\Tests\mma_contact\Functional\MmaContactTestBase;

/**
 * @coversDefaultClass \Drupal\mma_contact\Plugin\Block\AdditionalFieldsContactBlock
 * @group mma_contact
 */
class AdditionalFieldsContactBlockTest extends MmaContactTestBase {

  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'field'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    FieldStorageConfig::create([
      'entity_type' => 'contact_message',
      'type' => 'string',
      'field_name' => 'field_test',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'contact_message',
      'bundle' => 'test_contact',
      'field_name' => 'field_test',
    ])->save();

    $contact_form = ContactForm::load('test_contact');
    $contact_form->setThirdPartySetting('mma_contact', 'mapping', [
      'name' => 'firstName',
      'mail' => 'email',
      'field_test' => 'fieldTest',
    ]);

    $contact_form->save();

    EntityFormMode::create([
      'id' => 'contact_message.block_form',
      'targetEntityType' => 'contact_message',
    ])->save();

    $entity_form_display = EntityFormDisplay::create([
      'targetEntityType' => 'contact_message',
      'bundle' => 'test_contact',
      'mode' => 'block_form',
      'content' => [],
    ]);
    $entity_form_display->setComponent('field_test', [
      'type' => 'string_textfield',
    ]);
    $entity_form_display->save();

    $account = $this->drupalCreateUser(['administer contact forms', 'administer marketo', 'access site-wide contact form', 'administer blocks']);
    $this->drupalLogin($account);
  }

  /**
   * Tests the block UI.
   */
  public function testBlockSettings() {
    $edit = [
      'settings[contact_form]' => 'test_contact',
      'id' => 'test_block',
    ];

    $this->drupalPostForm('admin/structure/block/add/mma_contact_block__additional_field_values/tibco', $edit, 'Save block');

    $edit = [
      'settings[fields][subject][0][value]' => 'test subject',
      'settings[fields][message][0][value]' => 'test message',
      'settings[fields][field_test][0][value]' => 'test value',
    ];
    $this->drupalPostForm('admin/structure/block/manage/test_block', $edit, 'Save block');


    $block_settings = Block::load('test_block');
    $this->assertEquals([['value' => 'test value']], $block_settings->getPlugin()->getConfiguration()['fields']['field_test']);
  }

  /**
   * Tests that the preconfigured value is passed along to the lead data.
   */
  public function testBlockSubmission() {
    $this->placeBlock('mma_contact_block__additional_field_values', [
      'contact_form' => 'test_contact',
      'fields' => [
        'field_test' => [['value' => 'test_value']],
      ],
    ]);

    $this->drupalPostForm('', [
      'subject[0][value]' => 'test subject',
      'message[0][value]' => 'test message',
    ], 'Send message');

    $lead_data = \Drupal::state()->get(TestMarketoMaApiClient::class);
    $lead_data = array_filter($lead_data);
    $expected_lead_data = [
      'firstName' => $this->loggedInUser->getAccountName(),
      'email' => $this->loggedInUser->getEmail(),
      'fieldTest' => 'test_value',
    ];
    $this->assertEquals($expected_lead_data, reset($lead_data));
  }

}
