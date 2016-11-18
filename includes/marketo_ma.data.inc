<?php

/**
 * @file
 * Class and methods for interacting with the Marketo Data object.
 */

/**
 * Marketo Data Interface
 */
interface MarketoData {
  /**
   * Get raw data array.
   * @return mixed
   */
  public function getRawData();

  /**
   * Set data array.
   *
   * @param array $data
   */
  public function setData(array $data);

  /**
   * Get data array formatted per tracking method.
   * @return mixed
   */
  public function getFormattedData();

  /**
   * Set formatted data array.
   * @param $formatted_data
   */
  public function setFormattedData($formatted_data);

  /**
   * Get the email address.
   *
   * @return mixed
   */
  public function getEmailAddress();

  /**
   * Set the email address.
   *
   * @return mixed
   */
  public function setEmailAddress($email);

  /**
   * Get the email key.
   * @param null $tracking_method
   * @return mixed
   */
  public static function getEmailKey($tracking_method = null);
}

/**
 * Marketo MA Data
 */
class MarketoMaData implements MarketoData {
  // Marketo id|value data array.
  private $data;
  // Current tracking method.
  private $tracking_method;
  // Formatted data.
  private $formatted_data;

  /**
   * MarketoMaData constructor.
   *
   * @param array $data
   * @param string $tracking_method
   */
  public function __construct(array $data, $tracking_method = 'munchkin') {
    $this->setData($data);
    $this->setTrackingMethod($tracking_method);
    $this->getFormattedData();
  }

  /**
   * {@inheritdoc}
   */
  public function setData(array $data) {
    $this->data = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedData() {
    if (!$this->formatted_data) {
      $this->setFormattedData($this->formatData());
    }
    return $this->formatted_data;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawData() {
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function getTrackingMethod() {
    return $this->tracking_method;
  }

  /**
   * {@inheritdoc}
   */
  private function setTrackingMethod($tracking_method) {
    $this->tracking_method = $tracking_method;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmailAddress() {
    $email = NULL;
    $formatted_data = $this->getFormattedData();
    // Format the data based on tracking method.
    switch ($this->getTrackingMethod()) {
      case 'rest':
        if (!empty($formatted_data['email'])) {
          $email = $formatted_data['email'];
        }
        break;
      case 'munchkin':
      default:
        if (!empty($formatted_data['Email'])) {
          $email = $formatted_data['Email'];
        }
        break;
    }
    return $email;
  }

  /**
   * Format the data based on tracking method.
   *
   * @return array
   */
  public function formatData() {
    $raw_data = $this->getRawData();
    $formatted_data = array();
    // Format the data based on tracking method.
    switch ($this->getTrackingMethod()) {
      case 'rest':
        // Replace marketo_id with value of MARKETO_REST_LEAD_FIELD_REST_KEY.
        foreach ($raw_data as $id => $value) {
          // Get the row corresponding to the marketo id.
          $result = db_select(MARKETO_MA_SCHEMA_LEAD_FIELDS)
            ->fields(MARKETO_MA_SCHEMA_LEAD_FIELDS)
            ->condition(MARKETO_MA_LEAD_FIELD_ID, $id)
            ->execute()
            ->fetchAll();
          // Cycle through the result row(s) and set the new rest field key.
          foreach ($result as $field) {
            if (isset($field->{MARKETO_MA_LEAD_FIELD_REST_KEY})) {
              $formatted_data[$field->{MARKETO_MA_LEAD_FIELD_REST_KEY}] = $value;
            }
          }
        }
        break;
      case 'munchkin':
      default:
        // Replace marketo_id with value of MARKETO_REST_LEAD_FIELD_MUNCHKIN_KEY.
        foreach ($raw_data as $id => $value) {
          // Get the row corresponding to the marketo id.
          $result = db_select(MARKETO_MA_SCHEMA_LEAD_FIELDS)
            ->fields(MARKETO_MA_SCHEMA_LEAD_FIELDS)
            ->condition(MARKETO_MA_LEAD_FIELD_ID, $id)
            ->execute()
            ->fetchAll();
          // Cycle through the result row(s) and set the new munchkin field key.
          foreach ($result as $field) {
            if (isset($field->{MARKETO_MA_LEAD_FIELD_MUNCHKIN_KEY})) {
              $formatted_data[$field->{MARKETO_MA_LEAD_FIELD_MUNCHKIN_KEY}] = $value;
            }
          }
        }
        break;
    }
    return $formatted_data;
  }

  /**
   * {@inheritdoc}
   */
  public function setEmailAddress($email) {
    $formatted_data = $this->getFormattedData();
    // Format the data based on tracking method.
    switch ($this->getTrackingMethod()) {
      case 'rest':
        $formatted_data['email'] = $email;
        break;
      case 'munchkin':
      default:
        $formatted_data['Email'] = $email;
        break;
    }
    $this->setFormattedData($formatted_data);
  }

  /**
   * {@inheritdoc}
   */
  public function setFormattedData($formatted_data){
    $this->formatted_data = $formatted_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function getEmailKey($tracking_method = null) {
    $key = null;
    // Format the data based on tracking method.
    switch ($tracking_method) {
      case 'rest':
        $key = 'email';
        break;
      case 'munchkin':
      default:
        $key = 'Email';
        break;
    }

    return $key;
  }

}
