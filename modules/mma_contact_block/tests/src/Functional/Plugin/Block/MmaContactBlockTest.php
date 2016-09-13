<?php

namespace Drupal\Tests\mma_contact_block\Kernel\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\contact\Entity\ContactForm;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\marketo_ma\Lead;
use Drupal\mma_contact_test\TestMarketoMaApiClient;
use Drupal\simpletest\BlockCreationTrait;
use Drupal\Tests\mma_contact\Functional\MmaContactTestBase;

/**
 * @coversDefaultClass \Drupal\mma_contact_block\Plugin\Block\MmaContactBlock
 * @group mma_contact_block
 */
class MmaContactBlockTest extends MmaContactTestBase {

  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'field', 'contact_block', 'contact_storage', 'mma_contact', 'mma_contact_block'];

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
      'name' => '1',
      'mail' => '3',
      'field_test' => '4',
    ]);

    $contact_form->save();

    $account = $this->drupalCreateUser(['administer contact forms', 'administer marketo', 'access site-wide contact form', 'administer blocks']);
    $this->drupalLogin($account);
  }

  /**
   * Tests the block UI.
   */
  public function testBlockSettings() {
    // Get the block creation form.
    $this->drupalGet('admin/structure/block/add/mma_contact_block/seven');

    $edit = [
      'settings[contact_form]' => 'test_contact',
      'id' => 'test_block',
    ];

    $this->drupalPostForm('admin/structure/block/add/mma_contact_block/seven', $edit, 'Save block');

    $this->drupalGet('admin/structure/block/manage/test_block');

    // Make sure the fields form is displayed.
    $this->assertSession()->fieldNotExists('settings[fields][message]');
    $this->assertSession()->fieldExists('settings[fields][field_test]');

    $edit = [
      'settings[fields][field_test]' => 'test value',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save block');

    $block_settings = Block::load('test_block');
    $this->assertEquals('test value', $block_settings->getPlugin()->getConfiguration()['fields']['field_test']);
  }

  /**
   * Tests that the preconfigured value is passed along to the lead data.
   */
  public function testBlockSubmission() {
    $this->placeBlock('mma_contact_block', [
      'contact_form' => 'test_contact',
      'fields' => [
        'field_test' => 'test_value',
      ],
    ]);

    $this->drupalPostForm('', [
      'subject[0][value]' => 'test subject',
      'message[0][value]' => 'test message',
    ], 'Send message');

    $lead_data = \Drupal::state()->get(TestMarketoMaApiClient::class);
    $lead_data = array_filter($lead_data);
    $expected_lead_data = new Lead([
      'firstName' => $this->loggedInUser->getAccountName(),
      'email' => $this->loggedInUser->getEmail(),
      'fieldTest' => 'test_value',
    ]);

    $this->assertEquals($expected_lead_data, end($lead_data));
  }

}
