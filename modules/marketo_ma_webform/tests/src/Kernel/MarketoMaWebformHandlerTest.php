<?php

namespace Drupal\Tests\marketo_ma_webform\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\marketo_ma_webform\Plugin\WebformHandler\MarketoMaWebformHandler;
use Drupal\marketo_ma\Lead;

/**
 * MarketoMaWebformHandler kernel tests.
 *
 * @group marketo_ma_webform
 */
class MarketoMaWebformHandlerTest extends EntityKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['webform', 'marketo_ma', 'marketo_ma_webform'];

  /**
   * Tests getLead().
   */
  public function testGetLead() {
    // Get constructor args and mocks.
    $configuration = [
      'settings' => [
        'marketo_ma_mapping' => [
          'my_form_element_1' => 'email',
          'my_form_element_2' => 'firstName',
          'my_form_element_3' => 'lastName',
        ],
      ],
    ];
    $plugin_id = 'marketo_ma';
    $plugin_definition = '';
    $logger_factory = $this->getMockLoggerChannelFactory();
    $config_factory = $this->getMockConfigFactory();
    $entity_type_manager = $this->getMockEntityTypeManager();
    $conditions_validator = $this->getMockWebformSubmissionConditionsValidator();
    $marketo_ma_service = $this->getMockMarketoMaService();

    // Make getLead public.
    $method = new \ReflectionMethod('\Drupal\marketo_ma_webform\Plugin\WebformHandler\MarketoMaWebformHandler', 'getLead');
    $method->setAccessible(TRUE);

    // Instantiate handler.
    $handler = new MarketoMaWebformHandler($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator, $marketo_ma_service);

    // Mock webform submission.
    $webform_submission_array = [
      'my_form_element_1' => 'foo',
      'my_form_element_2' => 'bar',
      'my_form_element_3' => 'baz',
    ];
    $webform_submission = $this->getMockBuilder('\Drupal\webform\WebformSubmissionInterface')
      ->getMock();
    $webform_submission->expects($this->any())
      ->method('toArray')
      ->will($this->returnValue(['data' => $webform_submission_array]));

    // Mock enabled Marketo fields.
    $webform_config = $this->getMockConfigFactory();
    $webform_config->expects($this->any())
      ->method('get')
      ->will($this->returnValue([41 => 41, 43 => 43, 44 => 44]));
    $config_factory->expects($this->any())
      ->method('get')
      ->will($this->returnValue($webform_config));

    // Mock available Marketo fields.
    $marketo_fields = [
      'firstName' => [
        'id' => 41,
        'displayName' => 'First Name',
        'dataType' => 'string',
        'length' => '255',
        'restName' => 'firstName',
        'restReadOnly' => 0,
        'soapName' => 'FirstName',
        'soapReadOnly' => 0,
      ],
      'lastName' => [
        'id' => 43,
        'displayName' => 'Last Name',
        'dataType' => 'string',
        'length' => '255',
        'restName' => 'lastName',
        'restReadOnly' => 0,
        'soapName' => 'LastName',
        'soapReadOnly' => 0,
      ],
      'email' => [
        'id' => 44,
        'displayName' => 'Email Address',
        'dataType' => 'email',
        'length' => '255',
        'restName' => 'email',
        'restReadOnly' => 0,
        'soapName' => 'Email',
        'soapReadOnly' => 0,
      ],
      'Additional_Comments' => [
        'id' => 2565,
        'displayName' => 'Additional Comments:',
        'dataType' => 'text',
        'length' => '',
        'restName' => 'Additional_Comments',
        'restReadOnly' => 0,
        'soapName' => 'Additional_Comments',
        'soapReadOnly' => 0,
      ],
    ];
    $marketo_ma_service->expects($this->any())
      ->method('getMarketoFields')
      ->will($this->returnValue($marketo_fields));

    // Check the output.
    $lead = $method->invoke($handler, $webform_submission);
    $this->assertTrue($lead instanceof Lead);
    $this->assertEquals('foo', $lead->get('email'));
    $this->assertEquals('bar', $lead->get('firstName'));
    $this->assertEquals('baz', $lead->get('lastName'));
  }

  /**
   * Get a mock logger channel factory.
   */
  protected function getMockLoggerChannelFactory() {
    return $this->getMockBuilder('\Drupal\Core\Logger\LoggerChannelFactoryInterface')->getMock();
  }

  /**
   * Get a mock config factory.
   */
  protected function getMockConfigFactory() {
    return $this->getMockBuilder('\Drupal\Core\Config\ConfigFactoryInterface')->getMock();
  }

  /**
   * Get a mock entity type manager.
   */
  protected function getMockEntityTypeManager() {
    return $this->getMockBuilder('\Drupal\Core\Entity\EntityTypeManagerInterface')->getMock();
  }

  /**
   * Get a mock webform submission conditions validator.
   */
  protected function getMockWebformSubmissionConditionsValidator() {
    return $this->getMockBuilder('\Drupal\webform\WebformSubmissionConditionsValidatorInterface')->getMock();
  }

  /**
   * Get a mock Marketo MA service.
   */
  protected function getMockMarketoMaService() {
    return $this->getMockBuilder('\Drupal\marketo_ma\Service\MarketoMaServiceInterface')->getMock();
  }

}
