<?php

namespace Drupal\mma_user\Service;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\marketo_ma\Lead;
use Drupal\marketo_ma\Service\MarketoMaApiClientInterface;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Drupal\mma_user\ActivityType;
use Drupal\user\UserInterface;

class MarketoMaUserService implements MarketoMaUserServiceInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * The marketo MA API client service.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   */
  private $marketo_ma;

  /**
   * The marketo MA API client service.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaApiClientInterface
   */
  private $api_client;

  /**
   * The state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Creates the Marketo MA user core service..
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\marketo_ma\Service\MarketoMaServiceInterface $marketo_ma
   *   The Marketo MA service.
   * @param \Drupal\marketo_ma\Service\MarketoMaApiClientInterface $api_client
   *   The marketo ma api client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaServiceInterface $marketo_ma, MarketoMaApiClientInterface $api_client, StateInterface $state) {
    $this->config_factory = $config_factory;
    $this->marketo_ma = $marketo_ma;
    $this->api_client = $api_client;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function config() {
    // Use static caching.
    static $config = NULL;
    // Load config if not already loaded.
    if (empty($config)) {
      $config = $this->config_factory->get(MarketoMaUserServiceInterface::MMA_USER_CONFIG_NAME);
    }

    return $config;
  }


  /**
   * {@inheritdoc}
   */
  public function userLogin(UserInterface $account) {
    if (in_array('login', $this->config()->get('events'))) {
      $this->updateLead($account);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function userCreate(UserInterface $user) {
    if (in_array('create', $this->config()->get('events'))) {
      $this->updateLead($user);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function userUpdate(UserInterface $user) {
    if (in_array('update', $this->config()->get('events'))) {
      $this->updateLead($user);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateLead(UserInterface $user) {
    // Get the enabled fields from the marketo ma service.
    $enabled_fields = $this->marketo_ma->getEnabledFields();

    $mapping = $this->config()->get('mapping');
    $data = [];

    foreach ($mapping as $contact_field_name => $marketo_field_id) {
      // Make sure there is a value to set and the field is still enabled.
      if (($field_item = $user->get($contact_field_name)->first()) && isset($enabled_fields[$marketo_field_id])) {
        // Get the field name.
        $field_name = $enabled_fields[$marketo_field_id]->getFieldName($this->marketo_ma->trackingMethod());
        // Adds the field value to the mapped data.
        $data[$field_name] = $field_item->{$field_item->mainPropertyName()};
      }
    }

    if (!empty($data)) {
      // Let the Marketo MA module handle the rest.
      $this->marketo_ma->updateLead(new Lead($data));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMarketoActivities($reset = FALSE) {
    // Reset if requested or fields have never been retrieved.
    if ($reset || $this->state->get('mma_user.activity_types', FALSE) === FALSE) {
      // Get the fields.
      $activity_types = $this->api_client->canConnect() ? $this->api_client->getActivityTypes() : [];

      // Save the field options in state.
      if (!empty($activity_types)) {
        $marketo_ma_activity_types = [];
        // Convert response array to an array of MarketoField objects keyed by the marketo field id.
        foreach ($activity_types as $activity_type) {
          $marketo_ma_activity_types[$activity_type['id']] = new ActivityType($activity_type);
        }
        $this->state->set('mma_user.activity_types', $marketo_ma_activity_types);
      }
    }

    return $this->state->get('mma_user.activity_types', []);
  }

  /**
   * {@inheritdoc}
   */
  public function getActivitiesAsTableSelectOptions($reset = FALSE) {
    // Convert objects to table-select options.
    return array_map(function ($item) {
      return $item instanceof ActivityType
        ? $item->toTableSelectOption()
        : [];
    }, $this->getMarketoActivities($reset));
  }

}
