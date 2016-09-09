<?php

namespace Drupal\mma_user\Service;


use Drupal\Core\State\StateInterface;
use Drupal\marketo_ma\Service\MarketoMaApiClientInterface;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Drupal\mma_user\ActivityType;
use Drupal\user\UserInterface;

class MarketoMaUserService implements MarketoMaUserServiceInterface {

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
   * @param \Drupal\marketo_ma\Service\MarketoMaServiceInterface $marketo_ma
   *   The Marketo MA service.
   * @param \Drupal\marketo_ma\Service\MarketoMaApiClientInterface $api_client
   *   The marketo ma api client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(MarketoMaServiceInterface $marketo_ma, MarketoMaApiClientInterface $api_client, StateInterface $state) {
    $this->marketo_ma = $marketo_ma;
    $this->api_client = $api_client;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function userLogin(UserInterface $account) {
    $stop=1;
  }

  /**
   * {@inheritdoc}
   */
  public function userCreate(UserInterface $user) {
    $stop=1;
  }

  /**
   * {@inheritdoc}
   */
  public function userUpdate(UserInterface $user) {
    $stop=1;
  }

  /**
   * {@inheritdoc}
   */
  public function apiClientCanConnect() {
    return $this->api_client->canConnect();
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
