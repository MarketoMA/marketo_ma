<?php

namespace Drupal\marketo_ma_webform\Plugin\WebformHandler;

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
   * {@inheritdoc}
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
      'marketo_ma_mapping' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $webform = $this->getWebform();
    // Assemble webform mapping source fields.
    $map_sources = [];
    $elements = $this->webform->getElementsDecoded();
    foreach (Element::children($elements) as $key) {
      if (empty($elements[$key]['#title'])) {
        continue;
      }
      $map_sources[$key] = $elements[$key]['#title'];
    }
    /** @var \Drupal\webform\WebformSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityTypeManager()->getStorage('webform_submission');
    $field_definitions = $submission_storage->getFieldDefinitions();
    $field_definitions = $submission_storage->checkFieldDefinitionAccess($webform, $field_definitions);
    foreach ($field_definitions as $key => $field_definition) {
      $map_sources[$key] = $field_definition['title'] . ' (type : ' . $field_definition['type'] . ')';
    }
    $marketo_field_options = array_map(function ($marketo_field) {
      return sprintf('%s (%d)', $marketo_field['displayName'], $marketo_field['id']);
    }, (array) $this->marketoMaService->getAvailableFields());

    $form['marketo_ma_mapping'] = [
      '#type' => 'webform_mapping',
      '#title' => $this->t('Webform to Marketo MA Lead mapping'),
      '#description' => $this->t('Only Maps with specified "Marketo MA Lead Field" will be submitted to Marketo.'),
      '#source__title' => t('Webform Submitted Data'),
      '#destination__title' => t('Marketo MA Lead Field'),
      '#source' => $map_sources,
      '#destination__type' => 'webform_select_other',
      '#destination' => $marketo_field_options,
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
    // Get available Marketo fields.
    $marketo_ma_available_fields = $this->marketoMaService->getAvailableFields();
    // Assemble lead data.
    $configuration = $this->configuration['marketo_ma_mapping'];
    foreach ($configuration as $webform_field => $marketo_field) {
      $id = $marketo_field[$marketo_field]['id'];
      if (isset($marketo_ma_available_fields[$id])) {
        $lead_data[$marketo_field] = $webform_submission[$webform_field];
      }
    }
    // Build and return lead.
    $lead = new Lead($lead_data);
    return $lead;
  }

}
