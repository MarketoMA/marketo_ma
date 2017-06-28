<?php

namespace Drupal\marketo_ma;

/**
 * Provides a lead object for the Marketo MA module.
 *
 * @package Drupal\marketo_ma
 */
class Lead {

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
      $this->setCookie($_COOKIE['_mkto_trk']);
    }
  }

  /**
   * Get the Marketo MA lead's email address.
   *
   * @return string
   *   The Lead's email.
   */
  public function getEmail() {
    return isset($this->data['email']) ? $this->data['email'] : ($this->data['Email'] ? $this->data['Email'] : NULL);
  }

  /**
   * Get the current session "_mkto_trk" cookie value.
   *
   * @return string
   *   The Lead's email.
   */
  public function getCookie() {
    return $this->get('cookies');
  }

  /**
   * Sets the leads tracking cookie value.
   *
   * @param string $value
   *   The value from the "_mkto_trk" cookie.
   *
   * @return \Drupal\marketo_ma\Lead $this
   */
  public function setCookie($value) {
    $this->set('cookies', $value);
    return $this;
  }

  /**
   * Get the Marketo MA lead's Marketo ID.
   *
   * @return string
   *   The Lead's marketo ID.
   */
  public function id() {
    return $this->get('id');
  }

  /**
   * Get a specific lead value.
   *
   * @param string $data_key
   *   The key used to store the data. i.e. "email".
   *
   * @return mixed
   *   The requested value.
   */
  public function get($data_key) {
    return !empty($this->data[$data_key]) ? $this->data[$data_key] : NULL;

  }

  /**
   * Set a specific lead value.
   *
   * @param $data_key
   *   The key used to store the data. i.e. "email".
   * @param mixed $value
   *
   * @return \Drupal\marketo_ma\Lead
   *   The lead object.
   */
  public function set($data_key, $value) {
    $this->data[$data_key] = $value;
    return $this;
  }

  /**
   * Get all the data stored in this lead.
   *
   * @return array
   *   Lead data set.
   */
  public function data() {
    return $this->data;
  }

}
