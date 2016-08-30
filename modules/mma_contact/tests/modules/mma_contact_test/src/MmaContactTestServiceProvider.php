<?php

namespace Drupal\mma_contact_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides an alternative mma client service implementation.
 */
class MmaContactTestServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->getDefinition('marketo_ma.api_client')
      ->setClass(TestMarketoMaApiClient::class)
      ->setArguments([new Reference('state')]);
  }

}
