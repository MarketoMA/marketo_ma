<?php

namespace Drupal\mma_user\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\marketo_ma\MarketoFieldDefinition;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Drupal\mma_user\Service\MarketoMaUserServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MarketoMaUserSettings.
 *
 * @package Drupal\marketo_ma\Form
 */
class MarketoMaUserSettings extends ConfigFormBase {

  /**
   * The Marketo MA API client.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   */
  protected $mma_service;

  /**
   * The Marketo MA API client.
   *
   * @var \Drupal\mma_user\Service\MarketoMaUserServiceInterface
   */
  protected $mma_user_service;

  /**
   * Th entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entity_field_manager;

  /**
   * Constructs a \Drupal\marketo_ma\Form\MarketoMASettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\marketo_ma\Service\MarketoMaServiceInterface $mma_service
   *   The Marketo MA API client.
   * @param \Drupal\mma_user\Service\MarketoMaUserServiceInterface $mma_user_service
   *   The Marketo MA API client.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaServiceInterface $mma_service, MarketoMaUserServiceInterface $mma_user_service, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($config_factory);
    $this->mma_service = $mma_service;
    $this->mma_user_service = $mma_user_service;
    $this->entity_field_manager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('marketo_ma'),
      $container->get('marketo_ma.user'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'marketo_ma_user.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'marketo_ma_user_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var array $form */
    $form = parent::buildForm($form, $form_state);

    // Get the configuration.
    $config = $this->config('marketo_ma_user.settings');

    //<editor-fold desc="Form layout structure">
    $form['user_settings_tab'] = [
      '#title' => $this->t('User settings'),
      '#type' => 'details',
      '#group' => 'marketo_ma_tabs',
      '#weight' => 4,
    ];
    $form['user_settings_tab']['group_events'] = [
      '#title' => $this->t('Events'),
      '#type' => 'fieldset',
    ];
    $form['user_settings_tab']['group_mappings'] = [
      '#title' => $this->t('User field mapping'),
      '#type' => 'fieldset',
    ];
    $form['user_settings_tab']['group_activities'] = [
      '#title' => $this->t('Enabled Activities'),
      '#type' => 'fieldset',
      '#description' => $this->t('Note: Activities are retrieved by type in batches of 10 types. Each multiple of 10 activity types will require another API request and will afffect ther performance of activity pages.'),
    ];
    //</editor-fold>

    // Add the role tracking settings.
    $form['user_settings_tab']['group_events']['events'] = [
      '#type' => 'checkboxes',
      '#title' => t('Trigger a lead update on the following events:'),
      '#default_value' => $config->get('events'),
      '#options' => [
        'login' => $this->t('User login'),
        'create' => $this->t('User registration / creation'),
        'update' => $this->t('User update'),
      ],
    ];

    $form['user_settings_tab']['group_mappings']['mapping'] = [
      '#type' => 'table',
      '#header' => [
        'title' => $this->t('User Field'),
        'mapping' => $this->t('Marketo Field'),
      ],
      '#empty' => $this->t('There are no user fields available for mapping.'),
    ];

    // Get mappings from config
    $mapping = $config->get('mapping');
    // Get enabled marketo fields.
    $marketo_field_options = ['' => $this->t('None')] + $this->getMarketoFields();
    // Add an mapping select field for each user field.
    foreach ($this->getUserFields() as $field_name => $label) {
      $form['user_settings_tab']['group_mappings']['mapping'][$field_name] = [
        'title' => ['#markup' => $label],
        'mapping' => [
          '#type' => 'select',
          '#title' => $this->t('Select mapped component'),
          '#title_display' => 'hidden',
          '#options' => $marketo_field_options,
          '#default_value' => isset($mapping[$field_name]) ? $mapping[$field_name] : FALSE,
        ],
      ];
    }

    // Get fields options from the marketo ma service.
    $options = $this->mma_user_service->getActivitiesAsTableSelectOptions();

    // Only show the enabled options unless retrieving from marketo.
    if (!($trigger = $form_state->getTriggeringElement()) || end($trigger['#parents']) !== 'activity_api_retrieve_activities') {
      $options = array_intersect_key($options, $config->get('enabled_activities'));
    }
    $form['user_settings_tab']['group_activities']['enabled_activities'] = [
      '#type' => 'tableselect',
      '#title' => $this->t('Enabled Activities'),
      '#header' => [
        $this->t('ID'),
        $this->t('Name'),
        $this->t('Description'),
        $this->t('Primary attribute name'),
      ],
      '#options' => $options,
      '#empty' => $this->t('No activity types, try retrieving from marketo.'),
      '#prefix' => '<div id="marketo-enabled-activities-wrapper">',
      '#suffix' => '</div>',
      '#default_value' => $config->get('enabled_activities'),
    ];
    // Add the ajax button that get's fields from the marketo API.
    $form['user_settings_tab']['group_activities']['activity_api_retrieve_activities'] = [
      '#type' => 'button',
      '#value' => $this->t('Fetch from Marketo'),
      '#disabled' => !$this->mma_service->apiClientCanConnect(),
      '#ajax' => [
        'callback' => [$this, 'retrieveApiActivities'],
        'event' => 'mouseup',
        'wrapper' => 'marketo-enabled-activities-wrapper',
        'progress' => array(
          'type' => 'throbber',
          'message' => $this->t('Retrieving fields from Marketo...'),
        ),
      ],
    ];

    // Add the validation and submit callbacks.
    $form['#validate'][] = [$this, 'validateForm'];
    $form['#submit'][] = [$this, 'submitForm'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Get the mapping values.
    $mapping = array_map(function ($form_value) {
      return $form_value['mapping'];
    }, $form_state->getValue('mapping'));

    $this->config('marketo_ma_user.settings')
      ->set('events', array_filter($form_state->getValue('events')))
      ->set('mapping', array_filter($mapping))
      ->set('enabled_activities', array_filter($form_state->getValue('enabled_activities')))
      ->save();
  }

  /**
   * Connects to Marketo and retrieves the API activity types.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   *   The form element to replace in the ajax wrapper setting.
   */
  public function retrieveApiActivities(array &$form, FormStateInterface $form_state) {

    // Build an options array from the api response.
    $options = $this->mma_user_service->getActivitiesAsTableSelectOptions(TRUE);

    // Add the retrieved options.
    $form['user_settings_tab']['group_activities']['enabled_activities']['#options'] = $options;

    // Return the form element that will bre replaced in the wrapper element.
    return $form['user_settings_tab']['group_activities']['enabled_activities'];
  }


  /**
   * Returns the available marketo field labels, keyed by machine name.
   *
   * @return string[]
   */
  protected function getMarketoFields() {
    return array_map(function ($field) {
      return $field instanceof MarketoFieldDefinition ? $field->getDisplayName() : '';
    }, $this->mma_service->getEnabledFields());
  }

  /**
   * Returns all available user form field labels, keyed by machine name.
   *
   * @return string[]
   */
  protected function getUserFields() {
    $fields = $this->entity_field_manager->getFieldDefinitions('user', 'user');
    return array_map(function (FieldDefinitionInterface $field_definition) {
      return $field_definition->getLabel();
    }, $fields);
  }

}
