<?php

namespace Drupal\mma_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\marketo_ma\Service\MarketoMaApiClientInterface;
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
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entity_type_manager, MarketoMaApiClientInterface $api_client) {
    $this->entityTypeManager = $entity_type_manager;
    $this->api_client = $api_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('marketo_ma.api_client')
    );
  }

  /**
   * Viewlead.
   *
   * @return string
   *   Return Hello string.
   */
  public function viewLead(UserInterface $user) {
    $activity = $this->getLeadActivity($user->getEmail());
    if (!empty($activity)) {
      $header = ['one', 'two', 'three'];
      $rows = array(array(1, 2, 3), array(4, 5, 6), array(7, 8, 9));

      return [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
      ];
    } else {
      return ['#markup' => $this->t('No lead activity found for %username.', ['%username' => $user->getAccountName()])];
    }
  }

  /**
   * Gets the title for the view lead page.
   *
   * @return string
   *   Return Hello string.
   */
  public function viewLeadTitle(UserInterface $user) {
    return $this->t('Marketo Lead (:username)', [':username' => $user->getAccountName()]);
  }

  /**
   * Viewlead.
   *
   * @return string
   *   Return Hello string.
   */
  public function viewActivity(UserInterface $user) {
    $activity = $this->getLeadActivity($user->getEmail());
    if (!empty($activity)) {
      $header = ['one', 'two', 'three'];
      $rows = array(array(1, 2, 3), array(4, 5, 6), array(7, 8, 9));

      return [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
      ];
    } else {
      return ['#markup' => $this->t('No lead activity found for %username.', ['%username' => $user->getAccountName()])];
    }
  }

  /**
   * Gets the title for the view lead page.
   *
   * @return string
   *   Return Hello string.
   */
  public function viewActivityTitle(UserInterface $user) {
    return $this->t('Marketo Lead (:username)', [':username' => $user->getAccountName()]);
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
