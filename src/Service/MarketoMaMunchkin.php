<?php

namespace Drupal\marketo_ma\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\encryption\EncryptionTrait;
use Drupal\marketo_ma\Lead;

/**
 * The marketo MA munchkin service (marketo_ma.munchkin).
 */
class MarketoMaMunchkin implements MarketoMaMunchkinInterface {

  use EncryptionTrait;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

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
   * Creates the Marketo API client wrapper service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountInterface $current_user, RouteMatchInterface $route_match) {
    $this->config_factory = $config_factory;
    $this->current_user = $current_user;
    $this->route_match = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function config() {
    // Use static caching.
    static $config = NULL;
    // Load config if not already loaded.
    if (empty($config)) {
      $config = $this->config_factory->get(MarketoMaServiceInterface::MARKETO_MA_CONFIG_NAME);
    }

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccountID() {
    return $this->decrypt($this->config()->get('munchkin.account_id'));
  }

  /**
   * {@inheritdoc}
   */
  public function getLibrary() {
    return $this->config()->get('munchkin.javascript_library');
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigured() {
    return (!empty($this->decrypt($this->config()->get('munchkin.api_private_key')))
      && !empty($this->getAccountID())
      && !empty($this->getLibrary())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAction($action_type, Lead $lead, $args = []) {
    if ($action_type === MarketoMaMunchkinInterface::ACTION_ASSOCIATE_LEAD && !empty($lead->getEmail())) {
      // The `associateLead` action requires the email and signing.
      return [
        'action' => $action_type,
        'data' => $lead->data(),
        'hash' => hash('sha1', $this->decrypt($this->config()->get('munchkin.api_private_key')) . $lead->getEmail()),
      ];
    } else {
      // The cookie is used for identification. Only args are required.
      return [
        'action' => $action_type,
        'data' => $args,
      ];
    }
  }

}
