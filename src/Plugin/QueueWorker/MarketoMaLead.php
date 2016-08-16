<?php

namespace Drupal\marketo_ma\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Updates marketo lead.
 *
 * @QueueWorker(
 *   id = "marketo_ma_lead",
 *   title = @Translation("Marketo MA Lead"),
 *   cron = {"time" = 60}
 * )
 */
class MarketoMaLead extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // @todo: Add process logic.
  }

}
