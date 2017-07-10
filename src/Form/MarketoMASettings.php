<?php

namespace Drupal\marketo_ma\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\encryption\EncryptionTrait;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MarketoMASettings.
 *
 * @package Drupal\marketo_ma\Form
 */
class MarketoMASettings extends ConfigFormBase {

  use EncryptionTrait;

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
   * @param \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   *   The marketo ma service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaServiceInterface $service) {
    parent::__construct($config_factory);
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('marketo_ma')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME];
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

    $encryption_key = Settings::get('encryption_key');
    if (empty($encryption_key)) {
      drupal_set_message($this->t('Encryption key is required to be setup in settings.php first. Check Encryption module README file for instructions.'), 'warning');
    }

    // Get the configuration.
    $config = $this->config(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME);

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
      '#title' => $this->t('Field Definition'),
      '#type' => 'details',
      '#description' => $this->t('By default, all fields below will be available for mapping to Webform and User Profile fields. It is possible to limit the available fields by selecting them below. Read-only fields are displayed here but are never available for mapping.'),
      '#group' => 'marketo_ma_tabs',
    ];
    $form['page_tracking_tab'] = [
      '#title' => $this->t('Page tracking'),
      '#type' => 'details',
      '#group' => 'marketo_ma_tabs',
      '#description' => $this->t('On which pages should Marketo tracking take place.'),
    ];
    $form['role_tracking_tab'] = [
      '#title' => $this->t('Role tracking'),
      '#type' => 'details',
      '#group' => 'marketo_ma_tabs',
    ];
    $form['munchkin_account_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account ID'),
      // @see http://developers.marketo.com/blog/server-side-form-post/
      '#description' => $this->t('In Marketo, go to Admin > Munchkin and copy the Munchkin Account ID, which has the format of 000-AAA-000'),
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
    $form['marketo_ma_munchkin_advanced'] = [
      '#title' => $this->t('Advanced Initialization Parameters'),
      '#type' => 'details',
      '#description' => $this->t("Munchkin can accept a variety of additional configuration parameters to customize its behavior.<br />NOTE: Leave the field blank to accept it's default value as defined in munchkin.js"),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#group' => 'group_munchkin',
    ];
    $form['munchkin_partition'] = [
      '#type' => 'textfield',
      '#title' => $this->t('wsInfo - Workspace (Partition)'),
      '#default_value' => $config->get('munchkin.partition'),
      '#required' => FALSE,
      '#description' => $this->t('Takes a string to target a workspace.  This workspace ID is obtained by selecting the Workspace in the Admin -> Munchkin menu.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['munchkin_altIds'] = [
      '#type' => 'textfield',
      '#title' => $this->t('altIds'),
      '#default_value' => $config->get('munchkin.altIds'),
      '#required' => FALSE,
      '#description' => $this->t('Accepts an array of Munchkin ID strings.  When enabled, this will duplicate all Web Activity to the targeted subscriptions, based on their Munchkin Id.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['munchkin_cookieLifeDays'] = [
      '#type' => 'textfield',
      '#title' => $this->t('cookieLifeDays'),
      '#default_value' => $config->get('munchkin.cookieLifeDays'),
      '#required' => FALSE,
      '#description' => t('Sets the expiry date of any newly created munchkin tracking cookies to this many days in the future. Default is two years.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['munchkin_clickTime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('clickTime'),
      '#default_value' => $config->get('munchkin.clickTime'),
      '#required' => FALSE,
      '#description' => $this->t('Sets the number of miliseconds to block after a click to allow for click tracking request.  Reducing will reduce accuracy of click-tracking.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['munchkin_cookieAnon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('cookieAnon'),
      '#default_value' => $config->get('munchkin.cookieAnon'),
      '#required' => FALSE,
      '#description' => $this->t('Default true. If set to false, will prevent tracking and cookying of new anonymous leads.  Leads are cookied and tracked after filling out a Marketo form, or clicking through from a Marketo Email.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['munchkin_domainLevel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('domainLevel'),
      '#default_value' => $config->get('munchkin.domainLevel'),
      '#required' => FALSE,
      '#description' => $this->t('Default 3.  Setting to 2 allows for proper tracking on two-letter top-level domains.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['munchkin_disableClickDelay'] = [
      '#type' => 'textfield',
      '#title' => $this->t('disableClickDelay'),
      '#default_value' => $config->get('munchkin.disableClickDelay'),
      '#required' => FALSE,
      '#description' => $this->t('Default false.  If set to true, disables click tracking delay entirely.  Will reduce accuracy of click tracking.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['munchkin_asyncOnly'] = [
      '#type' => 'textfield',
      '#title' => $this->t('asyncOnly'),
      '#default_value' => $config->get('munchkin.asyncOnly'),
      '#required' => FALSE,
      '#description' => $this->t('Default false.  If set to true, will send XHRs asynchronously.'),
      '#group' => 'marketo_ma_munchkin_advanced',
    ];
    $form['api_tab']['group_rest'] = [
      '#title' => $this->t('REST API config'),
      '#description' => $this->t('You will need an api user and service configured for this application. See @link for details.', [
        '@link' => Link::fromTextAndUrl('Quick Start Guide for Marketo REST API', Url::fromUri('http://developers.marketo.com/blog/quick-start-guide-for-marketo-rest-api/'))->toString(),
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
      '#description' => $this->t('Client ID is established as part of a <a href="@url">Custom Service</a>.', ['@url' => 'http://developers.marketo.com/documentation/rest/custom-service/']),
    ];
    $form['api_tab']['group_rest']['rest_client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#description' => $this->t('The client secret for your rest api user.'),
      '#default_value' => $this->decrypt($config->get('rest.client_secret')),
      '#states' => [
        'required' => [':input[name=tracking_method]' => ['value' => 'api_client']],
      ],
      '#description' => $this->t('Client Secret is established as part of a <a href="@url">Custom Service</a>.', ['@url' => 'http://developers.marketo.com/documentation/rest/custom-service/']),
    ];
    $form['api_tab']['group_rest']['rest_batch_requests'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Batch API transactions'),
      '#description' => $this->t('Will queue activity and send data to Marketo when cron runs.'),
      '#default_value' => $config->get('rest.batch_requests'),
    ];

    // Build the headers.
    $header = [
      $this->t('Marketo ID'),
      $this->t('Display name'),
      $this->t('REST key'),
      $this->t('Munchkin key'),
    ];

    // Only show the enabled options unless retrieving from marketo.
    $trigger = $form_state->getTriggeringElement();
    if (!is_null($trigger) && in_array('field_api_retrieve_fields', $trigger['#array_parents'])) {
      $options = $this->service->resetMarketoFields()->getMarketoFieldsAsTableSelectOptions();
    }
    else {
      // Get fields from cache.
      $options = $this->service->getMarketoFieldsAsTableSelectOptions();
    }

    $form['field_tab']['field_enabled_fields'] = [
      '#type' => 'tableselect',
      '#title' => $this->t('Marketo fields'),
      '#description' => $this->t('Pipe "|" delimited strings of [API Name]|[Friendly Label]. Enter one field per line. This information can be found in the Marketo admin page at Admin > Field Management > Export Field Names.<p>Once API client settings have been configured, these fields can be automatically obtained from Marketo using the button below</p>'),
      '#header' => [
        'displayName' => $this->t('Display Name'),
        'id' => $this->t('ID'),
        'restName' => $this->t('REST Field'),
        'soapName' => $this->t('SOAP/Munchkin Field'),
      ],
      '#options' => $options,
      '#empty' => $this->t('No fields, try retrieving from Marketo.'),
      '#prefix' => '<div id="marketo-defined-fields-wrapper">',
      '#suffix' => '</div>',
      '#default_value' => $config->get('field.enabled_fields'),
    ];

    foreach ($this->service->getReadOnly() as $field_key) {
      $form['field_tab']['field_enabled_fields'][$field_key]['#disabled'] = TRUE;
    }

    // Add the ajax button that get's fields from the marketo API.
    $form['field_tab']['field_api_retrieve_fields'] = [
      '#type' => 'button',
      '#value' => $this->t('Retrieve from Marketo'),
      '#disabled' => !$this->service->apiClientCanConnect(),
      '#ajax' => [
        'callback' => [$this, 'retrieveApiFields'],
        'event' => 'mouseup',
        'wrapper' => 'marketo-defined-fields-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Retrieving fields from Marketo...'),
        ],
      ],
    ];

    $visibility_request_path_pages = $config->get('tracking.request_path.pages');

    $form['page_tracking_tab']['marketo_ma_visibility_pages'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add tracking to specific pages'),
      '#options' => [
        $this->t('Every page except the listed pages'),
        $this->t('The listed pages only'),
      ],
      '#default_value' => $config->get('tracking.request_path.mode'),
    ];
    $form['page_tracking_tab']['marketo_ma_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#title_display' => 'invisible',
      '#default_value' => !empty($visibility_request_path_pages) ? $visibility_request_path_pages : '',
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", ['%blog' => '/blog', '%blog-wildcard' => '/blog/*', '%front' => '<front>']),
      '#rows' => 10,
    ];

    $visibility_user_role_roles = $config->get('tracking.user_role.roles');

    $form['role_tracking_tab']['tracking_roles_visibility'] = [
      '#type' => 'radios',
      '#title' => $this->t('Add tracking for specific roles'),
      '#options' => [
        $this->t('Add to the selected roles only'),
        $this->t('Add to every role except the selected ones'),
      ],
      '#default_value' => $config->get('tracking.user_role.mode'),
    ];
    $form['role_tracking_tab']['tracking_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#default_value' => !empty($visibility_user_role_roles) ? $visibility_user_role_roles : [],
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', user_role_names()),
      '#description' => $this->t('If none of the roles are selected, all users will be tracked. If a user has any of the roles checked, that user will be tracked (or excluded, depending on the setting above).'),
    ];

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

    $this->config(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME)
      ->set('tracking_method', $form_state->getValue('tracking_method'))
      ->set('instance_host', $form_state->getValue('instance_host'))
      ->set('logging', $form_state->getValue('logging'))
      ->set('munchkin.lead_source', $form_state->getValue('munchkin_lead_source'))
      ->set('munchkin.account_id', $this->encrypt($form_state->getValue('munchkin_account_id')))
      ->set('munchkin.javascript_library', $form_state->getValue('munchkin_javascript_library'))
      ->set('munchkin.partition', $form_state->getValue('munchkin_partition'))
      ->set('munchkin.api_private_key', $this->encrypt($form_state->getValue('munchkin_api_private_key')))
      ->set('munchkin.altIds', $form_state->getValue('munchkin_altIds'))
      ->set('munchkin.cookieLifeDays', $form_state->getValue('munchkin_cookieLifeDays'))
      ->set('munchkin.clickTime', $form_state->getValue('munchkin_clickTime'))
      ->set('munchkin.cookieAnon', $form_state->getValue('munchkin_cookieAnon'))
      ->set('munchkin.domainLevel', $form_state->getValue('munchkin_domainLevel'))
      ->set('munchkin.disableClickDelay', $form_state->getValue('munchkin_disableClickDelay'))
      ->set('munchkin.asyncOnly', $form_state->getValue('munchkin_asyncOnly'))
      ->set('rest.batch_requests', $form_state->getValue('rest_batch_requests'))
      ->set('rest.client_id', $this->encrypt($form_state->getValue('rest_client_id')))
      ->set('rest.client_secret', $this->encrypt($form_state->getValue('rest_client_secret')))
      ->set('field.enabled_fields', array_filter($form_state->getValue('field_enabled_fields')))
      ->set('tracking.request_path.mode', $form_state->getValue('marketo_ma_visibility_pages'))
      ->set('tracking.request_path.pages', $form_state->getValue('marketo_ma_pages'))
      ->set('tracking.user_role.mode', $form_state->getValue('tracking_roles_visibility'))
      ->set('tracking.user_role.roles', array_filter($form_state->getValue('tracking_roles')))
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
    // Return the form element that will bre replaced in the wrapper element.
    return $form['field_tab']['field_enabled_fields'];
  }

}
