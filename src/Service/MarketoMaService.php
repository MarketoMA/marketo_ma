<?php

namespace Drupal\marketo_ma\Service;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\marketo_ma\FieldDefinitionSet;
use Drupal\user\PrivateTempStoreFactory;

/**
 * The marketo MA worker service is responsible for most of the work the module
 * performs.
 */
class MarketoMaService implements MarketoMaServiceInterface {

  use StringTranslationTrait;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The marketo MA API client service.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaApiClientInterface
   */
  private $apiClient;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The Marketo MA munchkin service.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaMunchkinInterface
   */
  protected $munchkin;

  /**
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Stores the tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Marketo lead fields.
   *
   * @var \Drupal\marketo_ma\FieldDefinitionSet
   */
  protected $fieldset;

  /**
   * Stores updateLead result.
   *
   * @var array|null
   */
  protected $updateLeadResult;

  /**
   * Creates the Marketo MA core service..
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\marketo_ma\Service\MarketoMaApiClientInterface $api_client
   *   The marketo ma api client.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\marketo_ma\Service\MarketoMaMunchkinInterface $munchkin
   *   The munchkin service.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue service.
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaApiClientInterface $api_client, AccountInterface $current_user, RouteMatchInterface $route_match, PathMatcherInterface $path_matcher, MarketoMaMunchkinInterface $munchkin, QueueFactory $queue_factory, PrivateTempStoreFactory $temp_store_factory, StateInterface $state) {
    $this->configFactory = $config_factory;
    $this->apiClient = $api_client;
    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
    $this->pathMatcher = $path_matcher;
    $this->munchkin = $munchkin;
    $this->queueFactory = $queue_factory;
    $this->tempStoreFactory = $temp_store_factory;
    $this->state = $state;
    $this->fieldset = new FieldDefinitionSet();
  }

  /**
   * {@inheritdoc}
   */
  public function trackingMethod() {
    return $this->config()->get('tracking_method');
  }

  /**
   * {@inheritdoc}
   */
  public function pageAttachments(&$page) {
    // Check whether we should track via the Munchkin.
    if ($this->shouldTrackCurrentRequest()) {
      // Add marketo ma to the page..
      if (!empty($this->munchkin->getAccountID())) {
        // Add the library and settings for tracking the page.
        $page['#attached']['library'][] = 'marketo_ma/marketo-ma';
        $page['#attached']['drupalSettings']['marketo_ma'] = [
          'track' => TRUE,
          'key' => $this->munchkin->getAccountID(),
          'initParams' => $this->munchkin->getInitParams(),
          'library' => $this->munchkin->getLibrary(),
        ];
      }

      // Get the Lead data from temporary user storage.
      $lead = $this->getUserData();

      // Check for the munchkin option and that the munchkin api is configured.
      if ($lead
        && $this->trackingMethod() == MarketoMaServiceInterface::TRACKING_METHOD_MUNCHKIN
        && $this->munchkin->isConfigured()
        && !empty($lead->getEmail())
      ) {
        // Set drupalSettings so JS will do the lead association.
        $page['#attached']['drupalSettings']['marketo_ma']['actions'][] = $this->munchkin->getAction(MarketoMaMunchkinInterface::ACTION_ASSOCIATE_LEAD, $lead);
        // Set the associated flag so we are not associating on every request.
        $this->resetUserData();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function config() {
    // Use static caching.
    static $config = NULL;
    // Load config if not already loaded.
    if (empty($config)) {
      $config = $this->configFactory->get(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME);
    }

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldTrackCurrentRequest() {
    $user = \Drupal::currentUser();
    return ($this->_marketo_ma_visibility_pages() && $this->_marketo_ma_visibility_roles($user));
  }

  /**
   * Tracking visibility check for pages.
   *
   * Based on visibility setting this function returns TRUE if JS code should
   * be added to the current page and otherwise FALSE.
   */
  private function _marketo_ma_visibility_pages() {
    static $page_match;

    // Cache visibility result if function is called more than once.
    if (!isset($page_match)) {
      $visibility_request_path_mode = $this->config()->get('tracking.request_path.mode');
      $visibility_request_path_pages = $this->config()->get('tracking.request_path.pages');

      // Match path if necessary.
      if (!empty($visibility_request_path_pages)) {
        // Convert path to lowercase. This allows comparison of the same path
        // with different case. Ex: /Page, /page, /PAGE.
        $pages = Unicode::strtolower($visibility_request_path_pages);
        if ($visibility_request_path_mode < 2) {
          // Compare the lowercase path alias (if any) and internal path.
          $path = \Drupal::service('path.current')->getPath();
          $path_alias = Unicode::strtolower(\Drupal::service('path.alias_manager')->getAliasByPath($path));
          $page_match = $this->pathMatcher->matchPath($path_alias, $pages) || (($path != $path_alias) && $this->pathMatcher->matchPath($path, $pages));
          // When $visibility_request_path_mode has a value of 0, the tracking
          // code is displayed on all pages except those listed in $pages. When
          // set to 1, it is displayed only on those pages listed in $pages.
          $page_match = !($visibility_request_path_mode xor $page_match);
        }
        else {
          $page_match = FALSE;
        }
      }
      else {
        $page_match = TRUE;
      }

    }
    return $page_match;
  }

  /**
   * Tracking visibility check for user roles.
   *
   * Based on visibility setting this function returns TRUE if JS code should
   * be added for the current role and otherwise FALSE.
   *
   * @param object $account
   *   A user object containing an array of roles to check.
   *
   * @return bool
   *   TRUE if JS code should be added for the current role and otherwise FALSE.
   */
  private function _marketo_ma_visibility_roles($account) {
    $enabled = $visibility_user_role_mode = $this->config()->get('tracking.user_role.mode');
    $visibility_user_role_roles = $this->config()->get('tracking.user_role.roles');

    if (count($visibility_user_role_roles) > 0) {
      // One or more roles are selected.
      foreach (array_values($account->getRoles()) as $user_role) {
        // Is the current user a member of one of these roles?
        if (in_array($user_role, $visibility_user_role_roles)) {
          // Current user is a member of a role that should be tracked/excluded
          // from tracking.
          $enabled = !$visibility_user_role_mode;
          break;
        }
      }
    }
    else {
      // No role is selected for tracking, therefore all roles should be tracked.
      $enabled = TRUE;
    }

    return $enabled;
  }

  /**
   * {@inheritdoc}
   */
  public function updateLead($lead) {
    // Get the tracking method.
    if ($this->trackingMethod() === MarketoMaServiceInterface::TRACKING_METHOD_API) {
      // Do we need to batch the lead update?
      if (!$this->config()->get('rest.batch_requests')) {
        // Just sync the lead now.
        $this->updateLeadResult = $this->api_client->syncLead($lead);
      }
      else {
        // Queue up the lead sync.
        $this->queue_factory->get('marketo_ma_lead')->createItem($lead);
      }

      $this->resetUserData();
    }
    else {
      // Set the user data so munchkin can take it from there.
      $this->setUserData($lead);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setUserData($lead) {
    // Make sure we have a real user before trying to access user data.
    if ($this->sessionAvailable()) {
      $this->temporaryStorage()->set('user_data', $lead);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserData() {
    return $this->temporaryStorage()->get('user_data');
  }

  /**
   * {@inheritdoc}
   */
  public function resetUserData() {
    // Make sure we have a real user before trying to access user data.
    if ($this->sessionAvailable()) {
      $this->temporaryStorage()->delete('user_data');
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasUserData() {
    return !empty($this->getUserData());
  }

  /**
   * {@inheritdoc}
   */
  public function getMarketoFields() {
    return $this->fieldset->getAll();
  }

  /**
   * {@inheritdoc}
   */
  public function getMarketoFieldsAsTableSelectOptions() {
    return $this->fieldset->getAllTableselect();
  }

  /**
   * {@inheritdoc}
   */
  public function getReadOnly() {
    return $this->fieldset->getReadOnly();
  }

  /**
   *
   */
  public function resetMarketoFields() {
    $api_fields = $this->apiClient->canConnect() ? $this->apiClient->getFields() : [];
    foreach ($api_fields as $api_field) {
      $this->fieldset->add($api_field);
    }
    $this->fieldset = new FieldDefinitionSet();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledFields() {
    return array_intersect_key($this->getMarketoFields(), (array) $this->config()->get('field.enabled_fields'));
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableFields() {
    $writableFields = $this->fieldset->getWriteable();
    if ($enabledFields = $this->config()->get('field.enabled_fields')) {
      return array_intersect_key($writableFields, $enabledFields);
    }
    return $writableFields;
  }

  /**
   * Gets the private temporary storage for the marketo_ma module.
   *
   * @return \Drupal\user\PrivateTempStore
   */
  protected function temporaryStorage() {
    return $this->tempStoreFactory->get('marketo_ma');
  }

  /**
   * {@inheritdoc}
   */
  public function apiClientCanConnect() {
    return $this->apiClient->canConnect();
  }

  /**
   * Check whether user data is available for the current user.
   *
   * @return bool
   */
  protected function sessionAvailable() {
    return ($this->currentUser->id() || \Drupal::requestStack()->getCurrentRequest()->getSession());
  }

  /**
   * {@inheritdoc}
   */
  public function getUpdateLeadResult() {
    return $this->updateLeadResult;
  }

}
