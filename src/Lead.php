<?php

namespace Drupal\marketo_ma;

/**
 * Provides a lead object for the Marketo MA module.
 *
 * @package Drupal\marketo_ma
 */
class Lead implements LeadInterface, \Serializable {

  /**
   * The lead data.
   *
   * @var array
   */
  protected $data;

  /**
   * Constructs a \Drupal\marketo_ma\Lead object.
   *
   * @param array $lead_data
   *   Initial lead data.
   */
  public function __construct($lead_data = []) {
    // Set the lead data for this lead.
    $this->data = $lead_data;
    // Set the marketo tracking cookie if it is available.
    if (!empty($_COOKIE['_mkto_trk'])) {
      $this->data['cookie'] = $_COOKIE['_mkto_trk'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    return $this->get('email');
  }

  /**
   * {@inheritdoc}
   */
  public function getCookie() {
    return $this->get('cookie');
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->get('id');
  }

  /**
   * {@inheritdoc}
   */
  public function get($data_key) {
    return !empty($this->data[$data_key]) ? $this->data[$data_key] : NULL;

  }

  /**
   * {@inheritdoc}
   */
  public function set($data_key, $value) {
    $this->data[$data_key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function data() {
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function serialize() {
    return serialize($this->getAll());
  }

  /**
   * {@inheritdoc}
   */
  public function unserialize($data) {
    $this->data = unserialize($data);
  }
}
