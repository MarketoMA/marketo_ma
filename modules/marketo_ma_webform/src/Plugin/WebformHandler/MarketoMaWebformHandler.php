<?php

namespace Drupal\marketo_ma_webform\Plugin\WebformHandler;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Drupal\marketo_ma\Lead;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Render\Element;

/**
 * Marketo MA Webform Handler.
 *
 * @WebformHandler(
 *   id = "marketo_ma",
 *   label = @Translation("Marketo MA"),
 *   category = @Translation("External"),
 *   description = @Translation("Sends a webform submission via Marketo MA."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class MarketoMaWebformHandler extends WebformHandlerBase {

  /**
   * The Marketo MA service.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   */
  protected $marketoMaService;

  /**
   * MarketoMaWebformHandler constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\webform\WebformSubmissionConditionsValidatorInterface $conditions_validator
   * @param \Drupal\marketo_ma\Service\MarketoMaServiceInterface $marketo_ma_service
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, WebformSubmissionConditionsValidatorInterface $conditions_validator, MarketoMaServiceInterface $marketo_ma_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator);
    $this->marketoMaService = $marketo_ma_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('webform_submission.conditions_validator'),
      $container->get('marketo_ma')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $configuration = $this->getConfiguration();
    return [
      '#settings' => $configuration['settings'],
    ] + parent::getSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'formid' => '',
      'marketo_ma_mapping' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $webform = $this->getWebform();
    // Assemble webform mapping source fields.
    $mapSources = [];
    $elements = $this->webform->getElementsDecoded();
    foreach (Element::children($elements) as $key) {
      if (empty($elements[$key]['#title'])) {
        continue;
      }
      $mapSources[$key] = $elements[$key]['#title'];
    }
    $fieldDefinitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('webform_submission', $webform->id());
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $fieldDefinitions */
    $fieldDefinitions = $this->submissionStorage->checkFieldDefinitionAccess($webform, $fieldDefinitions);
    foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
      if (!$fieldDefinition instanceof BaseFieldDefinition) {
        $mapSources[$fieldName] = sprintf('%s (type: %s)', $fieldDefinition->getLabel(), $fieldDefinition->getType());
      }
    }
    $marketoFieldOptions = array_map(function ($marketoField) {
      return sprintf('%s (%d)', $marketoField['displayName'], $marketoField['id']);
    }, $this->marketoMaService->getAvailableFields());

    $form['formid'] = [
      '#type' => 'textfield',
      '#title' => t('Form ID'),
      '#description' => 'Specify this to use Forms2',
      '#default_value' => $this->configuration['formid'] ?? '',
    ];

    $form['marketo_ma_mapping'] = [
      '#type' => 'webform_mapping',
      '#title' => $this->t('Webform to Marketo MA Lead mapping'),
      '#description' => $this->t('Only Maps with specified "Marketo MA Lead Field" will be submitted to Marketo.'),
      '#source__title' => t('Webform Submitted Data'),
      '#destination__title' => t('Marketo MA Lead Field'),
      '#source' => $mapSources,
      '#destination__type' => 'webform_select_other',
      '#destination' => $marketoFieldOptions,
      '#default_value' => $this->configuration['marketo_ma_mapping'],
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->applyFormStateToConfiguration($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $is_completed = ($webform_submission->getState() == WebformSubmissionInterface::STATE_COMPLETED);
    if ($is_completed) {
      $lead = $this->getLead($webform_submission);
      if (isset($this->configuration['formid'])) {
        $lead->setFormId($this->configuration['formid']);
      }
      $this->marketoMaService->updateLead($lead);
      $result = $this->marketoMaService->getUpdateLeadResult();
      // Log message in Drupal's log.
      $context = [
        '@form' => $this->getWebform()->label(),
        'link' => $this->getWebform()->toLink($this->t('Edit'), 'handlers')->toString(),
      ];
      if (!$result) {
        $this->getLogger()->error('@form webform failed to sync a Marketo MA lead', $context);
      }
      else {
        $context['@lead_id'] = $result[0]['id'];
        $this->getLogger()->notice('@form webform synced Marketo MA lead @lead_id', $context);
      }
    }
  }

  /**
   * Creates a Marketo MA Lead populated with mapped form data.
   *
   * @return \Drupal\marketo_ma\Lead
   *   The Marketo lead to sync.
   */
  protected function getLead(WebformSubmissionInterface $webform_submission) {
    $lead_data = [];
    // Flatten submission data.
    $webform_submission = $webform_submission->toArray(TRUE);
    $webform_submission = $webform_submission['data'] + $webform_submission;
    unset($webform_submission['data']);
    // The webform mapping uses Marketo field IDs (integers), and the
    // lead capture endpoint requires SOAP-style names.
    $fields = $this->marketoMaService->getMarketoFields();
    $lead_data = [];
    foreach ($webform_submission as $webform_field_name => $value) {
      if (isset($this->configuration['marketo_ma_mapping'][$webform_field_name])) {
        $id = $this->configuration['marketo_ma_mapping'][$webform_field_name];
        $soap_name = $fields[$id]['soapName'];
        $lead_data[$soap_name] = $value;
      }
    }
    // Build and return lead.
    $lead = new Lead($lead_data);
    return $lead;
  }

}
