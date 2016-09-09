<?php

namespace Drupal\mma_user\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
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
   * @var \Drupal\mma_user\Service\MarketoMaUserServiceInterface
   */
  protected $mma_user_service;

  /**
   * Constructs a \Drupal\marketo_ma\Form\MarketoMASettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\mma_user\Service\MarketoMaUserServiceInterface $mma_user_service
   *   The Marketo MA API client.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaUserServiceInterface $mma_user_service) {
    parent::__construct($config_factory);
    $this->mma_user_service = $mma_user_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('marketo_ma.user')
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

    $form['user_settings_tab']['group_mappings']['mappings'] = [

    ];

    // Get fields options from the marketo ma service.
    $options = $this->mma_user_service->getActivitiesAsTableSelectOptions();

    // Only show the enabled options unless retrieving from marketo.
    if (!($trigger = $form_state->getTriggeringElement()) || end($trigger['#parents']) !== 'activity_api_retrieve_fields') {
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
    $form['user_settings_tab']['group_activities']['activity_api_retrieve_fields'] = [
      '#type' => 'button',
      '#value' => $this->t('Retrieve from Marketo'),
      '#disabled' => !$this->mma_user_service->apiClientCanConnect(),
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

    $this->config('marketo_ma_user.settings')
      ->set('events', array_filter($form_state->getValue('events')))
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

}
