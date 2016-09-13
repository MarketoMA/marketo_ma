<?php

namespace Drupal\mma_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\marketo_ma\Service\MarketoMaApiClientInterface;
use Drupal\mma_user\Service\MarketoMaUserServiceInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class MmaUserLeadDataController.
 *
 * @package Drupal\mma_user\Controller
 */
class MmaUserLeadDataController extends ControllerBase {

  /**
   * The marketo ma API client.
   *
   * @var \Drupal\marketo_ma\Service\MarketoMaApiClientInterface
   */
  protected $api_client;

  /**
   * The marketo ma user service.
   *
   * @var \Drupal\mma_user\Service\MarketoMaUserServiceInterface
   */
  protected $mma_user_service;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entity_type_manager, MarketoMaApiClientInterface $api_client, MarketoMaUserServiceInterface $mma_user_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->api_client = $api_client;
    $this->mma_user_service = $mma_user_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('marketo_ma.api_client'),
      $container->get('marketo_ma.user')
    );
  }

  /**
   * Title callback for the lead page.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   */
  public function viewLeadTitle(UserInterface $user) {
    return $this->t('Marketo Lead (:username)', [':username' => $user->getAccountName()]);
  }

  /**
   * Controller callback to view Marketo lead data.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   *
   * @return array
   *   A table render array of activity for the user.
   */
  public function viewLead(UserInterface $user) {
    // Get the lead from the user's email address.
    $lead = $this->api_client->getLeadByEmail($user->getEmail());
    $header = [$this->t('Marketo field name'), $this->t('Submitted information')];
    // Convert the lead data to table rows.
    $rows = !$lead ? [] : array_map(function ($field_value, $field_name) {
      return [
        $this->t(':value', [':value' => $field_name]),
        $this->t(':value', [':value' => $field_value]),
      ];
    }, $lead->data(), array_keys($lead->data()));

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No lead information found for %username.', ['%username' => $user->getEmail()]),
    ];
  }

  /**
   * Title callback for the lead activity page.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   */
  public function viewActivityTitle(UserInterface $user) {
    return $this->t('Marketo Lead Activity (:username)', [':username' => $user->getAccountName()]);
  }

  /**
   * Controller callback to view lead activity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   *
   * @return array
   *   A table render array of activity for the user.
   */
  public function viewActivity(UserInterface $user) {
    // Get the lead from the user's email address.
    $lead = $this->api_client->getLeadByEmail($user->getEmail());
    // Get the enabled activities.
    $enabled_activities = $this->mma_user_service->config()->get('enabled_activities');
    $activity = $this->api_client->getLeadActivity($lead, $enabled_activities);
    // Create the headers row.
    $header = [
      $this->t('Marketo activity ID'),
      $this->t('Date/Time'),
      $this->t('Activity Type'),
      $this->t('Asset Name'),
    ]; //ID	Date/Time	Activity Type	Asset Name
    // Convert the lead data to table rows.
    $rows = array_map(function ($activity) {
      return [
        $this->t(':value', [':value' => $activity['id']]),
        $this->t(':value', [':value' => $activity['activityDate']]),
        $this->t(':value', [':value' => $activity['activityType']]),
        $this->t(':value', [':value' => $activity['primaryAttributeValue']]),
      ];
    }, $activity);

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No lead activity found for %username.', ['%username' => $user->getAccountName()]),
    ];
  }


  /**
   * Gets activity given a leads email address.
   *
   * @param string $email
   *   The lead's email address.
   *
   * @return array
   *   Activities for the lead.
   */
  protected function getLeadActivity($email) {
    // Make sure marketo api client is configured.
    if ($this->api_client->canConnect()) {
      // Get the lead for this user.
      if ($lead = $this->api_client->getLeadByEmail($email)) {
        // Get the lead activity for the lead.
        $activity = $this->api_client->getLeadActivity($lead);
      }
    }
    return !empty($activity) ? $activity : [];
  }

}
