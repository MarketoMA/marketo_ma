<?php

namespace Drupal\Tests\marketo_ma\Kernel;

use Drupal\marketo_ma\MarketoFieldDefinition;
use Drupal\marketo_ma\Service\MarketoFieldSetInterface;

/**
 * @group marketo_ma
 */
class MarketoFieldsetTest extends MarketoMaKernelTestBase {

  /**
   * @var \Drupal\marketo_ma\Service\MarketoFieldSetInterface
   */
  protected $fieldset;

  protected $fields;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'system',
    'marketo_ma',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installSchema('system', 'key_value');

    $this->fieldset = \Drupal::service('marketo_ma.fieldset');

    $this->fields = [
      1234 => new MarketoFieldDefinition([
        'id' => 1234,
        'displayName' => 'First Name',
        'dataType' => "string",
        'rest' => [
          'name' => 'firstName',
          'readOnly' => FALSE,
        ],
        'soap' => [
          'name' => 'firstname',
          'readOnly' => FALSE,
        ],
      ]),
      1235 => new MarketoFieldDefinition([
        'id' => 1235,
        'displayName' => 'Last Name',
        'dataType' => "string",
        'rest' => [
          'name' => 'lastName',
          'readOnly' => FALSE,
        ],
        'soap' => [
          'name' => 'lastname',
          'readOnly' => FALSE,
        ],
      ]),
    ];
  }

  /**
   * Tests the marketo_ma service.
   */
  public function testFieldsetService() {
    self::assertTrue($this->fieldset instanceof MarketoFieldSetInterface);
  }

  public function testFieldStorage() {
    // Set the fields.
    $this->fieldset->setMultiple($this->fields);

    $retrieved_fields = $this->fieldset->getAll();

    self::assertSame($this->fields, $retrieved_fields);
    self::assertInstanceOf(MarketoFieldDefinition::class, reset($retrieved_fields));

    $this->fieldset->deleteMultiple(array_keys($this->fields));

    self::assertEmpty($this->fieldset->getAll());
  }

  public function testDeleteAll() {
    $this->fieldset->setMultiple($this->fields);
    self::assertNotEmpty($this->fieldset->getAll());

    $this->fieldset->deleteAll();
    self::assertEmpty($this->fieldset->getAll());
  }

  /**
   * Tests that getting one item after a cache reset doesn't trip up static
   * caching for getAll.
   */
  public function testGetOne() {
    $this->fieldset->setMultiple($this->fields);
    $this->fieldset->resetCache();
    $first = $this->fieldset->get(array_keys($this->fields)[0]);
    self::assertSame(1234, $first->id());

    // Make sure all items are still returned.
    self::assertSame($this->fields, $this->fieldset->getAll());
  }

}
