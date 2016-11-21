<?php

namespace Drupal\marketo_ma\Service;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\State\State;

/**
 * Provides the state system using a key value store.
 */
class MarketoFieldSet extends State implements MarketoFieldSetInterface {

  /**
   * Constructs a MarketoFieldSet object.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key value store to use.
   */
  function __construct(KeyValueFactoryInterface $key_value_factory) {
    $this->keyValueStore = $key_value_factory->get('marketo_ma.fieldset');
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    // Use static caching for all ids.
    static $all_ids;

    // Check for static cached values.
    if (empty($this->cache) || array_keys($this->cache) !== $all_ids) {
      $this->cache = $this->keyValueStore->getAll();
      // Static cache the ids for all items so we know we have all items.
      $all_ids = array_keys($this->cache);
    }

    return $this->cache;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    // Delete all values.
    $this->keyValueStore->deleteAll();
    $this->cache = [];
  }
}
