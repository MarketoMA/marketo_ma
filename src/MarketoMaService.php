<?php

namespace Drupal\marketo_ma;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
  protected $config_factory;

  /**
   * The marketo MA API client service.
   *
   * @var \Drupal\marketo_ma\MarketoMaApiClientInterface
   */
  private $api_client;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $current_user;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route_match;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $path_matcher;

  /**
   * The Marketo MA munchkin service.
   *
   * @var \Drupal\marketo_ma\MarketoMaMunchkinInterface
   */
  protected $munchkin;

  /**
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue_factory;

  /**
   * Stores the tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $temp_store_factory;

  /**
   * The state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Creates the Marketo API client wrapper service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\marketo_ma\MarketoMaApiClientInterface $api_client
   *   The marketo ma api client.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\marketo_ma\MarketoMaMunchkinInterface $munchkin
   *   The munchkin service.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue service.
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaApiClientInterface $api_client, AccountInterface $current_user, RouteMatchInterface $route_match, PathMatcherInterface $path_matcher, MarketoMaMunchkinInterface $munchkin, QueueFactory $queue_factory, PrivateTempStoreFactory $temp_store_factory, StateInterface $state) {
    $this->config_factory = $config_factory;
    $this->api_client = $api_client;
    $this->current_user = $current_user;
    $this->route_match = $route_match;
    $this->path_matcher = $path_matcher;
    $this->munchkin = $munchkin;
    $this->queue_factory = $queue_factory;
    $this->temp_store_factory = $temp_store_factory;
    $this->state = $state;
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
          'library' => $this->munchkin->getLibrary(),
        ];
      }

      // Get the Lead data from temporary user storage.
      if (! $lead = $this->getUserData()) {
        $lead = new Lead();
      }

      // Check for logged in user.
      if (empty($lead->getEmail()) && !empty($this->current_user->getEmail())) {
        $lead->set('email', $this->current_user->getEmail());
      }

      // Check for the munchkin option and that the munchkin api is configured.
      if ($this->trackingMethod() == MarketoMaServiceInterface::TRACKING_METHOD_MUNCHKIN
        && $this->munchkin->isConfigured()
        && !empty($lead->getEmail())
        && $lead->get('associated') !== TRUE
      ) {
        // Set drupalSettings so JS will do the lead association.
        $page['#attached']['drupalSettings']['marketo_ma']['actions'][] = $this->munchkin->getAction(MarketoMaMunchkinInterface::ACTION_ASSOCIATE_LEAD, $lead);
        // Set the associated flag so we are not associating on every request.
        $this->setUserData($lead->set('associated', TRUE));
      }
      // Check for the api option and that the client can connect.
      elseif ($this->trackingMethod() == MarketoMaServiceInterface::TRACKING_METHOD_API
        && $this->api_client->canConnect()
        && !empty($lead->getEmail())
        && $lead->get('associated') !== TRUE
      ) {
        // Use the API to associate the lead.
        $this->updateLead($lead);
        // Set the associated flag so we are not associating on every request.
        $this->setUserData($lead->set('associated', TRUE));
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
      $config = $this->config_factory->get('marketo_ma.settings');
    }

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldTrackCurrentRequest() {
    // Get track-able roles.
    $trackable_roles = array_filter($this->config()->get('tracking.roles'));
    // Get the current user's roles.
    $user_roles = $this->current_user->getRoles();
    // Checks if the current user has any trackable roles.
    if (empty(array_intersect(array_keys($trackable_roles), $user_roles))) {
      return FALSE;
    }

    // Get whether we are looking for a page match or a lack thereof.
    $negate_page_match = $this->config()->get('tracking.request_path.negate');
    // Use the patch matcher service to test whether the current path matches.
    $path_has_match = $this->path_matcher->matchPath($this->route_match->getRouteObject()->getPath() , $this->config()->get('tracking.request_path.pages'));

    return (($path_has_match && !$negate_page_match) || (!$path_has_match && $negate_page_match));
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
        $this->api_client->syncLead($lead);
      } else {
        // Queue up the lead sync.
        $this->queue_factory->get('marketo_ma_lead')->createItem($lead);
      }

      $this->resetUserData();
    } else {
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
    if (!empty($this->current_user->getLastAccessedTime())) {
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
    if (!empty($this->current_user->getLastAccessedTime())) {
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
  public function getMarketoFields($reset = FALSE) {
    // Reset if requested or fields have never been retrieved.
    if ($reset || $this->state->get('marketo_ma.field.defined_fields', FALSE) === FALSE) {
      // Get the fields.
      $api_fields = $this->api_client->canConnect() ? $this->api_client->getFields() : [];

      // Save the field options in state.
      if (!empty($api_fields)) {
        $marketo_ma_fields = [];
        // Convert response array to an array of MarketoField objects keyed by the marketo field id.
        foreach ($api_fields as $api_field) {
          $marketo_ma_fields[$api_field['id']] = new MarketoFieldDefinition($api_field);
        }
        $this->state->set('marketo_ma.field.defined_fields', $marketo_ma_fields);
      }
    }

    return $this->state->get('marketo_ma.field.defined_fields', []);
  }

  /**
   * {@inheritdoc}
   */
  public function getMarketoFieldsAsTableSelectOptions($reset = FALSE) {
    // Convert objects to table-select options.
    return array_map(function ($item) {
      return $item instanceof MarketoFieldDefinition
        ? $item->toTableSelectOption()
        : [];
    }, $this->getMarketoFields($reset));
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledFields() {
    return array_intersect_key($this->getMarketoFields(), (array) $this->config()->get('field.enabled_fields'));
  }

  /**
   * Gets the private temporary storage for the marketo_ma module.
   *
   * @return \Drupal\user\PrivateTempStore
   */
  protected function temporaryStorage() {
    return $this->temp_store_factory->get('marketo_ma');
  }

}
