<?php

namespace Drupal\marketo_ma;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * The marketo MA worker service is responsible for most of the work the module
 * performs.
 */
class MarketoMaService implements MarketoMaServiceInterface {

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
  private $client;

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
   * Creates the Marketo API client wrapper service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\marketo_ma\MarketoMaApiClientInterface $client
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\marketo_ma\MarketoMaMunchkinInterface $munchkin
   *   The munchkin service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MarketoMaApiClientInterface $client, AccountInterface $current_user, RouteMatchInterface $route_match, PathMatcherInterface $path_matcher, MarketoMaMunchkinInterface $munchkin) {
    $this->config_factory = $config_factory;
    $this->client = $client;
    $this->current_user = $current_user;
    $this->route_match = $route_match;
    $this->path_matcher = $path_matcher;
    $this->munchkin = $munchkin;
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
    if ($this->trackCurrentRequest()) {
      // Check for the munchkin option and that the munchkin api is configured.
      if ($this->trackingMethod() == 'munchkin' && $this->munchkin->isConfigured()) {
        // Add the library and settings for tracking the page.
        $page['#attached']['library'][] = 'marketo_ma/marketo-ma';
        $page['#attached']['drupalSettings']['marketo_ma'] = [
          'track' => TRUE,
          'key' => $this->munchkin->getAccountID(),
          'library' => $this->munchkin->getLibrary(),
        ];
      }
      // Check for the api option and that the client can connect.
      elseif ($this->trackingMethod() == 'api_client' && $this->client->canConnect()) {
        $this->apiTrackPage();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preProcessPage(&$variables) {
    // @todo: Remove if not needed.
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
  public function trackCurrentRequest() {
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
    // Get pages from config.
    $pages = $this->config()->get('tracking.request_path.pages');

    // Use the patch matcher service to test whether the current path matches.
    $path_has_match = $this->path_matcher->matchPath($this->route_match->getRouteObject()->getPath(), $pages);

    return (($path_has_match && !$negate_page_match) || (!$path_has_match && $negate_page_match));
  }

  /**
   * Tracks the page via the API client.
   */
  protected function apiTrackPage() {

  }

}
