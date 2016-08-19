<?php

namespace Drupal\marketo_ma;

/**
 * Interface for a marketo lead.
 *
 * @package Drupal\marketo_ma
 */
interface LeadInterface {

  /**
   * Get the Marketo MA lead's email address.
   *
   * @return string
   *   The Lead's email.
   */
  public function id();

  /**
   * Get the Marketo MA lead's email address.
   *
   * @return string
   *   The Lead's email.
   */
  public function getEmail();

  /**
   * Get the current session "_mkto_trk" cookie value.
   *
   * @return string
   *   The Lead's email.
   */
  public function getCookie();

  /**
   * Get all the data stored in this lead.
   *
   * @return array
   *   Lead data set.
   */
  public function data();

  /**
   * Get a specific lead value.
   *
   * @param string $data_key
   *   The key used to store the data. i.e. "email".
   * @return mixed
   *   The requested value.
   */
  public function get($data_key);

  /**
   * Set a specific lead value.
   *
   * @param $data_key
   *   The key used to store the data. i.e. "email".
   * @param mixed $value
   *
   * @return $this
   *   The lead object.
   */
  public function set($data_key, $value);

}
