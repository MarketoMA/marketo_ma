<?php

namespace Drupal\marketo_ma\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\encryption\EncryptionTrait;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\marketo_ma\Service\MarketoMaApiClientInterface;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MarketoSettings.
 *
 * @package Drupal\marketo_ma\Form
 */
class MarketoMASettings extends ConfigFormBase {

  use EncryptionTrait;

  /**
   * The Marketo MA API client.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaApiClientInterface
   */
  protected $client;

  /**
   * The Marketo MA core service.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   */
  protected $service;

  /**
   * Constructs a \Drupal\marketo_ma\Form\MarketoMASettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\marketo_ma\Service\MarketoMaApiClientInterface $marketo_ma_api_client
   *   The Marketo MA API client.
   * @param \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   *   The marketo ma service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaApiClientInterface $marketo_ma_api_client, MarketoMaServiceInterface $service) {
    parent::__construct($config_factory);
    $this->client = $marketo_ma_api_client;
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('marketo_ma.api_client'),
      $container->get('marketo_ma')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'marketo_ma.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'marketo_ma_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var array $form */
    $form = parent::buildForm($form, $form_state);

    // Get the configuration.
    $config = $this->config('marketo_ma.settings');

    //<editor-fold desc="Form layout structure">
    $form['marketo_ma_basic'] = [
      '#title' => $this->t('Basic Settings'),
      '#type' => 'fieldset',
    ];
    $form['marketo_ma_tabs'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'tab_api',
    ];
    $form['api_tab'] = [
      '#title' => $this->t('API Configuration'),
      '#type' => 'details',
      '#group' => 'marketo_ma_tabs',
    ];
    $form['field_tab'] = [
      '#title' => t('Field Definition'),
      '#type' => 'details',
      '#description' => $this->t('The fields defined here will be available for mapping.'),
      '#group' => 'marketo_ma_tabs',
    ];
    $form['page_tracking_tab'] = [
      '#title' => t('Page tracking'),
      '#type' => 'details',
      '#group' => 'marketo_ma_tabs',
      '#description' => $this->t('On which pages should Marketo tracking take place.'),
    ];
    $form['role_tracking_tab'] = [
      '#title' => t('Role tracking'),
      '#type' => 'details',
      '#group' => 'marketo_ma_tabs',
    ];
    //</editor-fold>

    //<editor-fold desc="Basic settings">
    $form['munchkin_account_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account ID'),
      // @see http://developers.marketo.com/blog/server-side-form-post/
      '#description' => t('In Marketo, go to Admin > Munchkin and copy the Munchkin Account ID, which has the format of 000-AAA-000'),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => $this->decrypt($config->get('munchkin.account_id')),
      '#group' => 'marketo_ma_basic',
    ];
    $form['munchkin_javascript_library'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Munchkin Javascript Library'),
      '#default_value' => $config->get('munchkin.javascript_library'),
      '#required' => TRUE,
      '#description' => $this->t('Typically this does not need to be changed and should use the default value //munchkin.marketo.net/munchkin.js'),
      '#group' => 'marketo_ma_basic',
    ];
    $form['instance_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Marketo Instance Host'),
      '#default_value' => $config->get('instance_host'),
      '#required' => FALSE,
      '#description' => $this->t('Host for your Marketo instance. Example: app-sjqe.marketo.com. Used for Forms 2.0 API.'),
      '#group' => 'marketo_ma_basic',
    ];
    $form['munchkin_lead_source'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Lead Source'),
      '#default_value' => $config->get('munchkin.lead_source'),
      '#description' => $this->t('If set, LeadSource will be set to this value unless specifically overridden during data collection.'),
      '#group' => 'marketo_ma_basic',
    ];
    $form['logging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Verbose Logging'),
      '#default_value' => $config->get('logging'),
      '#description' => $this->t('If checked, additional data will be added to watchdog.'),
      '#group' => 'marketo_ma_basic',
    ];
    //</editor-fold>

    //<editor-fold desc="API configuration">
    $form['api_tab']['tracking_method'] = [
      '#type' => 'radios',
      '#title' => $this->t('Tracking Method'),
      '#description' => $this->t(':desc<br /><ol><li>:opt1</li><li>:opt2</li></ol>', [
        ':desc' => 'Select how tracking should be handled.',
        ':opt1' => 'Munchkin API | Client side JS.',
        ':opt2' => 'REST API | Via the REST API on the server side.',
      ]),
      '#options' => [
        MarketoMaServiceInterface::TRACKING_METHOD_MUNCHKIN => $this->t('Munchkin Javascript API'),
        MarketoMaServiceInterface::TRACKING_METHOD_API => $this->t('REST API'),
      ],
      '#default_value' => $config->get('tracking_method'),
      '#required' => TRUE,
    ];

    //<editor-fold desc="Munchkin Configuration">
    $form['api_tab']['group_munchkin'] = [
      '#title' => $this->t('Munchkin Javascript API'),
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [':input[name=tracking_method]' => ['value' => 'munchkin']],
      ],
    ];

    $form['api_tab']['group_munchkin']['munchkin_api_private_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Private Key'),
      '#default_value' => $this->decrypt($config->get('munchkin.api_private_key')),
      '#description' => $this->t('Value can be found on the Munchkin Admin page at Admin > Integration > Munchkin'),
      '#states' => [
        'required' => [':input[name=tracking_method]' => ['value' => 'munchkin']],
      ],
    ];
    $form['api_tab']['group_munchkin']['munchkin_partition'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Workspace (Partition)'),
      '#default_value' => $config->get('munchkin.partition'),
      '#required' => FALSE,
      '#description' => $this->t('Value can be found on the Munchkin Admin page at Admin > Integration > Munchkin'),
    ];
    //</editor-fold>

    //<editor-fold desc="REST configuration">
    $form['api_tab']['group_rest'] = [
      '#title' => $this->t('REST API config'),
      '#description' => $this->t('You will need an api user and service configured for this application. See !link for details.', [
        '!link' => Link::fromTextAndUrl('Quick Start Guide for Marketo REST API', Url::fromUri('http://developers.marketo.com/blog/quick-start-guide-for-marketo-rest-api/'))->toString(),
      ]),
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [':input[name=tracking_method]' => ['value' => 'api_client']],
      ],
    ];
    $form['api_tab']['group_rest']['rest_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Id'),
      '#description' => $this->t('The client id for your rest api user.'),
      '#default_value' => $this->decrypt($config->get('rest.client_id')),
      '#states' => [
        'required' => [':input[name=tracking_method]' => ['value' => 'api_client']],
      ],
    ];
    $form['api_tab']['group_rest']['rest_client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#description' => $this->t('The client secret for your rest api user.'),
      '#default_value' => $this->decrypt($config->get('rest.client_secret')),
      '#states' => [
        'required' => [':input[name=tracking_method]' => ['value' => 'api_client']],
      ],
    ];
    $form['api_tab']['group_rest']['rest_batch_requests'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Batch API transactions'),
      '#description' => $this->t('Will queue activity and send data to Marketo when cron runs.'),
      '#default_value' => $config->get('rest.batch_requests'),
    ];
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="Field Definition config">

    // Build the headers.
    $header = [
      $this->t('Marketo ID'),
      $this->t('Display name'),
      $this->t('REST key'),
      $this->t('Munchkin key'),
    ];
    // Get fields options from the marketo ma service.
    $options = $this->service->getMarketoFieldsAsTableSelectOptions();

    $form['field_tab']['field_enabled_fields'] = [
      '#type' => 'tableselect',
      '#title' => t('Marketo fields'),
      '#description' => $this->t('Pipe "|" delimited strings of [API Name]|[Friendly Label]. Enter one field per line. This information can be found in the Marketo admin page at Admin > Field Management > Export Field Names.<p>Once API client settings have been configured, these fields can be automatically obtained from Marketo using the button below</p>'),
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No fields, Try retrieving from marketo.'),
      '#prefix' => '<div id="marketo-defined-fields-wrapper">',
      '#suffix' => '</div>',
      '#default_value' => $config->get('field.enabled_fields'),
    ];

    // Add the ajax button that get's fields from the marketo API.
    $form['field_tab']['field_api_retrieve_fields'] = [
      '#type' => 'button',
      '#value' => $this->t('Retrieve from Marketo'),
      '#disabled' => !$this->client->canConnect(),
      '#ajax' => [
        'callback' => [$this, 'retrieveApiFields'],
        'event' => 'mouseup',
        'wrapper' => 'marketo-defined-fields-wrapper',
        'progress' => array(
          'type' => 'throbber',
          'message' => $this->t('Retrieving fields from Marketo...'),
        ),
      ],
    ];
    //</editor-fold>

    //<editor-fold desc="Page tracking config">
    $form['page_tracking_tab']['tracking_request_path_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#default_value' => $config->get('tracking.request_path.pages'),
      '#rows' => 10,
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", [
        '%blog' => '/blog',
        '%blog-wildcard' => '/blog/*',
        '%front' => '<front>',
      ]),
    ];
    $form['page_tracking_tab']['tracking_request_path_negate'] = [
      '#type' => 'radios',
      '#options' => [
        0 => $this->t('Track on the listed pages'),
        1 => $this->t('Do not track on the listed pages'),
      ],
      '#default_value' => $config->get('tracking.request_path.negate'),
      '#required' => TRUE,
    ];
    //</editor-fold>

    //<editor-fold desc="Role tracking config">
    // Get the user roles to use as options.
    $options = \user_roles();
    // We don't need the Role entity, just the label.
    array_walk($options, function (&$item) {
      $item = $item->label();
    });
    // Add the role tracking settings.
    $form['role_tracking_tab']['tracking_roles'] = [
      '#type' => 'checkboxes',
      '#title' => t('Add tracking to specific roles'),
      '#default_value' => $config->get('tracking.roles'),
      '#options' => $options,
      '#description' => $this->t("Specify roles to be tracked, Warning: %warning", [
        '%warning' => 'If Anonymous user is unchecked, tracking history will not be available once the user logs in.',
      ]),
    ];
    //</editor-fold>

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

    $this->config('marketo_ma.settings')
      ->set('tracking_method', $form_state->getValue('tracking_method'))
      ->set('instance_host', $form_state->getValue('instance_host'))
      ->set('logging', $form_state->getValue('logging'))
      ->set('munchkin.lead_source', $form_state->getValue('munchkin_lead_source'))
      ->set('munchkin.account_id', $this->encrypt($form_state->getValue('munchkin_account_id')))
      ->set('munchkin.javascript_library', $form_state->getValue('munchkin_javascript_library'))
      ->set('munchkin.partition', $form_state->getValue('munchkin_partition'))
      ->set('munchkin.api_private_key', $this->encrypt($form_state->getValue('munchkin_api_private_key')))
      ->set('rest.batch_requests', $form_state->getValue('rest_batch_requests'))
      ->set('rest.client_id', $this->encrypt($form_state->getValue('rest_client_id')))
      ->set('rest.client_secret', $this->encrypt($form_state->getValue('rest_client_secret')))
      ->set('field.enabled_fields', array_filter($form_state->getValue('field_enabled_fields')))
      ->set('tracking.request_path.pages', $form_state->getValue('tracking_request_path_pages'))
      ->set('tracking.request_path.negate', $form_state->getValue('tracking_request_path_negate'))
      ->set('tracking.roles', array_filter($form_state->getValue('tracking_roles')))
      ->save();
  }

  /**
   * Connects to Marketo and retrieves the API fields.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   *   The form element to replace in the ajax wrapper setting.
   */
  public function retrieveApiFields(array &$form, FormStateInterface $form_state) {

    // Build an options array from the api response.
    $options = $this->service->getMarketoFieldsAsTableSelectOptions(TRUE);

    // Reset the defined fields value.
    $form['field_tab']['field_enabled_fields']['#value'] = [];
    $form['field_tab']['field_enabled_fields']['#options'] = $options;

    // Return the form element that will bre replaced in the wrapper element.
    return $form['field_tab']['field_enabled_fields'];
  }

}
