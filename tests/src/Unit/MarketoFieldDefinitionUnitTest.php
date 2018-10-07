<?php

namespace Drupal\Tests\marketo_ma\Unit;

use Drupal\marketo_ma\MarketoFieldDefinition;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\marketo_ma\MarketoFieldDefinition
 *
 * @group marketo_ma
 */
class MarketoFieldDefinitionUnitTest extends UnitTestCase {

  protected $field_data;

  /**
   *
   */
  public function setUp() {
    parent::setUp();

    $this->field_data = [
      'id' => 1,
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
    ];

  }

  /**
   *
   */
  public function testSerialization() {

    $field = new MarketoFieldDefinition($this->field_data);

    self::assertEquals($this->field_data['id'], $field->id());

    $serialized_field = serialize($field);

    self::assertEquals($field, unserialize($serialized_field));

  }

  /**
   *
   */
  public function testFieldName() {
    $field = new MarketoFieldDefinition($this->field_data);

    self::assertEquals($this->field_data['rest']['name'], $field->getFieldName(MarketoMaServiceInterface::TRACKING_METHOD_API));
    self::assertEquals($this->field_data['soap']['name'], $field->getFieldName(MarketoMaServiceInterface::TRACKING_METHOD_MUNCHKIN));

  }

}
