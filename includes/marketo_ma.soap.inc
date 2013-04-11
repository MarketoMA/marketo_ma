<?php

/**
 * @file
 * Class and methods for interacting with the Marketo SOAP API.
 */

interface MarketoInterface {

  /**
   * Retrieves list of defined fields.
   */
  public function getFields();

  /**
   * Retrieves lead information.
   * 
   * @param string $key
   * @param string $type
   */
  public function getLead($key, $type);

  /**
   * Inserts or updates a lead.
   * 
   * @param array $lead
   * @param string $key
   * @param type $cookie
   */
  public function syncLead($lead, $key, $cookie);
}

class MarketoClient implements MarketoInterface {

  // http://app.marketo.com/soap/mktows/2_0?WSDL
  protected $userId;
  protected $encryptionKey;
  protected $endpoint;

  /**
   * Constructor for MarketoClient class.
   * 
   * @param string $user_id
   * @param string $encryption_key
   * @param string $endpoint
   */
  public function __construct($user_id, $encryption_key, $endpoint) {
    $this->userId = $user_id;
    $this->encryptionKey = $encryption_key;
    $this->endpoint = $endpoint;

    $wsdl = $this->endpoint . '?WSDL';

    $options = array("connection_timeout" => 20, "location" => $this->endpoint);

    $this->client = new SoapClient($wsdl, $options);
  }

  /**
   * Builds Marketo authentication header.
   * 
   * @return \SoapHeader
   */
  protected function authenticationHeader() {
    $timestamp = date("c");
    $encrypt_string = $timestamp . $this->userId;
    $signature = hash_hmac('sha1', $encrypt_string, $this->encryptionKey);

    $data = new stdClass();
    $data->mktowsUserId = $this->userId;
    $data->requestSignature = $signature;
    $data->requestTimestamp = $timestamp;

    $header = new SoapHeader('http://www.marketo.com/mktows/', 'AuthenticationHeader', $data);

    return $header;
  }

  /**
   * Sends request to Marketo.
   * 
   * @param string $operation
   * @param array $params
   * @return object
   */
  protected function request($operation, $params) {
    return $this->client->__soapCall(
        $operation, array($params), array(), $this->authenticationHeader()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLead($key, $type = 'email') {
    $lead = new stdClass();
    $lead->leadKey = new stdClass();
    $lead->leadKey->keyType = strtoupper($type);
    $lead->leadKey->keyValue = $key;

    try {
      $result = $this->request('getLead', $lead);
      $leads = $this->prepareLeadResults($result);
    }
    catch (Exception $e) {

      if (isset($e->detail->serviceException->code) && $e->detail->serviceException->code == MarketoError::ERR_LEAD_NOT_FOUND) {
        // No leads were found.
        $leads = array();
      }
      else {
        throw new Exception($e);
      }
    }

    return $leads;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $params = array(
      'objectName' => 'LeadRecord',
    );
    try {
      $result = $this->request('describeMObject', $params);
      $fields = $this->prepareFieldResults($result);
    }
    catch (Exception $e) {
      $fields = array();
    }

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function syncLead($lead, $key = NULL, $cookie = NULL) {
    $params = new stdClass();
    $params->marketoCookie = $cookie;
    $params->returnLead = FALSE;
    $params->leadRecord = $this->leadRecord($lead, $key);

    try {
      $result = $this->request('syncLead', $params);
    }
    catch (Exception $e) {
      $result = FALSE;
    }
    return $result;
  }

  /**
   * Converts response into a more useful structure.
   * 
   * @param object $data
   * 
   * @return array
   */
  protected function prepareFieldResults($data) {
    $fields = array();

    foreach ($data->result->metadata->fieldList->field as $field) {
      $fields[$field->name] = $field->displayName;
    }

    return $fields;
  }

  /**
   * Creates proper structure for submitting a lead.
   * 
   * @param array $attributes
   * @param string $key
   * 
   * @return \stdClass
   */
  protected function leadRecord($attributes, $key = NULL) {
    $record = new stdClass();

    if ($key) {
      if (is_numeric($key)) {
        $record->Id = $key;
      }
      else {
        $record->Email = $key;
      }
    }

    $record->leadAttributeList = new stdClass();
    $record->leadAttributeList->attribute = array();

    foreach ($attributes as $attribute => $value) {
      $lead_attribute = new stdClass();
      $lead_attribute->attrName = $attribute;
      $lead_attribute->attrValue = $value;

      array_push($record->leadAttributeList->attribute, $lead_attribute);
    }

    return $record;
  }

  /**
   * Parses lead results into a more useful format.
   * 
   * @param object $marketo_result
   *   SOAP response
   * 
   * @return array
   *   An array of objects defining lead data
   */
  protected function prepareLeadResults($marketo_result) {
    $leads = array();

    if (is_object($marketo_result->result->leadRecordList->leadRecord)) {
      $marketo_result->result->leadRecordList->leadRecord = array($marketo_result->result->leadRecordList->leadRecord);
    }

    foreach ($marketo_result->result->leadRecordList->leadRecord as $lead) {
      foreach ($lead->leadAttributeList->attribute as $attribute) {
        $lead->attributes[$attribute->attrName] = $attribute->attrValue;
      }
      unset($lead->leadAttributeList);
      $leads[] = $lead;
    }

    return $leads;
  }

}

class MarketoError {

  const ERR_SEVERE_INTERNAL_ERROR = 10001;
  const ERR_INTERNAL_ERROR = 20011;
  const ERR_REQUEST_NOT_UNDERSTOOD = 20012;
  const ERR_ACCESS_DENIED = 20013;
  const ERR_AUTH_FAILED = 20014;
  const ERR_REQUEST_LIMIT_EXCEEDED = 20015;
  const ERR_REQ_EXPIRED = 20016;
  const ERR_INVALID_REQ = 20017;
  const ERR_BAD_ENCODING = 20018;
  const ERR_UNSUPPORTED_OP = 20019;
  const ERR_LEAD_KEY_REQ = 20101;
  const ERR_LEAD_KEY_BAD = 20102;
  const ERR_LEAD_NOT_FOUND = 20103;
  const ERR_LEAD_DETAIL_REQ = 20104;
  const ERR_LEAD_ATTRIB_BAD = 20105;
  const ERR_LEAD_SYNC_FAILED = 20106;
  const ERR_ACTIVITY_KEY_BAD = 20107;
  const ERR_PARAMETER_REQ = 20109;
  const ERR_PARAMETER_BAD = 20110;
  const ERR_LIST_NOT_FOUND = 20111;
  const ERR_CAMP_NOT_FOUND = 20113;
  const ERR_BAD_PARAMETER = 20114;
  const ERR_BAD_STREAM_POS = 20122;
  const ERR_STREAM_AT_END = 20123;

}
