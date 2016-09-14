<?php

namespace Drupal\mma_user\Service;

use Drupal\user\UserInterface;

interface MarketoMaUserServiceInterface {

  /** The Marketo MA User module config name. */
  const MMA_USER_CONFIG_NAME = 'mma_user.settings';

  /**
   * Gets the Marketo MA user config object.
   *
   * @return \Drupal\Core\Config\ImmutableConfig|null
   *   The `mma_user.settings` config object.
   */
  public function config();

  /**
   * Callback for `hook_user_login`.
   *
   * @param \Drupal\user\UserInterface $account
   *
   * @return mixed
   */
  public function userLogin(UserInterface $account);

  /**
   * Callback for `hook_entity_create`.
   *
   * @param \Drupal\user\UserInterface $user
   *
   * @return mixed
   */
  public function userCreate(UserInterface $user);

  /**
   * Callback for `hook_entity_update`.
   *
   * @param \Drupal\user\UserInterface $user
   *
   * @return mixed
   */
  public function userUpdate(UserInterface $user);

  /**
   * Get's activity types that are defined in marketo.
   *
   * @param boolean $reset
   *   Whether to try to refresh the list form the API client.
   *
   * @return \Drupal\mma_user\ActivityType[]
   *   All marketo activity types keyed by the marketo activity ID.
   */
  public function getMarketoActivities($reset = FALSE);

  /**
   * Get's all marketo activity types converted to table select options.
   *
   * @param boolean $reset
   *   Whether to try to refresh the activity types form the API client.
   *
   * @return \Drupal\mma_user\ActivityType[]
   *   All marketo activity types keyed by the marketo activity ID.
   */
  public function getActivitiesAsTableSelectOptions($reset = FALSE);

}

