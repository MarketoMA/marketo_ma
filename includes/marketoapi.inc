<?php

// PHP classes corresponding to the data types in defined in WSDL
class ActivityRecord {

  /**
   * @var int
   */
  public $id;

  /**
   * @var dateTime
   */
  public $activityDateTime;

  /**
   * @var string
   */
  public $activityType;

  /**
   * @var string
   */
  public $mktgAssetName;

  /**
   * @var (object)ArrayOfAttribute
   */
  public $activityAttributes;

  /**
   * @var string
   */
  public $campaign;

  /**
   * @var string
   */
  public $personName;

  /**
   * @var string
   */
  public $mktPersonId;

  /**
   * @var string
   */
  public $foreignSysId;

  /**
   * @var string
   */
  public $orgName;

  /**
   * @var string
   */
  public $foreignSysOrgId;

}

class ActivityTypeFilter {

  /**
   * @var (object)ArrayOfActivityType
   */
  public $includeTypes;

  /**
   * @var (object)ArrayOfActivityType
   */
  public $excludeTypes;

}

class Attribute {

  /**
   * @var string
   */
  public $attrName;

  /**
   * @var string
   */
  public $attrType;

  /**
   * @var string
   */
  public $attrValue;

}

class AuthenticationHeaderInfo {

  /**
   * @var string
   */
  public $mktowsUserId;

  /**
   * @var string
   */
  public $requestSignature;

  /**
   * @var string
   */
  public $requestTimestamp;

  /**
   * @var string
   */
  public $audit;

  /**
   * @var int
   */
  public $mode;

}

class CampaignRecord {

  /**
   * @var int
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $description;

}

class LeadActivityList {

  /**
   * @var int
   */
  public $returnCount;

  /**
   * @var int
   */
  public $remainingCount;

  /**
   * @var (object)StreamPosition
   */
  public $newStartPosition;

  /**
   * @var (object)ArrayOfActivityRecord
   */
  public $activityRecordList;

}

class LeadChangeRecord {

  /**
   * @var int
   */
  public $id;

  /**
   * @var dateTime
   */
  public $activityDateTime;

  /**
   * @var string
   */
  public $activityType;

  /**
   * @var string
   */
  public $mktgAssetName;

  /**
   * @var (object)ArrayOfAttribute
   */
  public $activityAttributes;

  /**
   * @var string
   */
  public $campaign;

  /**
   * @var string
   */
  public $mktPersonId;

}

class LeadKey {

  /**
   * @var string
   *     NOTE: keyType should follow the following restrictions
   *     You can have one of the following value
   *     IDNUM
   *     COOKIE
   *     EMAIL
   *     LEADOWNEREMAIL
   *     SFDCACCOUNTID
   *     SFDCCONTACTID
   *     SFDCLEADID
   *     SFDCLEADOWNERID
   *     SFDCOPPTYID
   */
  public $keyType;

  /**
   * @var string
   */
  public $keyValue;

}

class LeadRecord {

  /**
   * @var int
   */
  public $Id;

  /**
   * @var string
   */
  public $Email;

  /**
   * @var string
   */
  public $ForeignSysPersonId;

  /**
   * @var string
   *     NOTE: ForeignSysType should follow the following restrictions
   *     You can have one of the following value
   *     CUSTOM
   *     SFDC
   *     NETSUITE
   */
  public $ForeignSysType;

  /**
   * @var (object)ArrayOfAttribute
   */
  public $leadAttributeList;

}

class LeadStatus {

  /**
   * @var (object)LeadKey
   */
  public $leadKey;

  /**
   * @var boolean
   */
  public $status;

}

class ListKey {

  /**
   * @var string
   *     NOTE: keyType should follow the following restrictions
   *     You can have one of the following value
   *     MKTOLISTNAME
   *     MKTOSALESUSERID
   *     SFDCLEADOWNERID
   */
  public $keyType;

  /**
   * @var string
   */
  public $keyValue;

}

class ResultGetCampaignsForSource {

  /**
   * @var int
   */
  public $returnCount;

  /**
   * @var (object)ArrayOfCampaignRecord
   */
  public $campaignRecordList;

}

class ResultGetLead {

  /**
   * @var int
   */
  public $count;

  /**
   * @var (object)ArrayOfLeadRecord
   */
  public $leadRecordList;

}

class ResultGetLeadChanges {

  /**
   * @var int
   */
  public $returnCount;

  /**
   * @var int
   */
  public $remainingCount;

  /**
   * @var (object)StreamPosition
   */
  public $newStartPosition;

  /**
   * @var (object)ArrayOfLeadChangeRecord
   */
  public $leadChangeRecordList;

}

class ResultGetMultipleLeads {

  /**
   * @var int
   */
  public $returnCount;

  /**
   * @var int
   */
  public $remainingCount;

  /**
   * @var string
   */
  public $newStreamPosition;

  /**
   * @var (object)ArrayOfLeadRecord
   */
  public $leadRecordList;

}

class ResultListOperation {

  /**
   * @var boolean
   */
  public $success;

  /**
   * @var (object)ArrayOfLeadStatus
   */
  public $statusList;

}

class ResultRequestCampaign {

  /**
   * @var boolean
   */
  public $success;

}

class ResultSyncLead {

  /**
   * @var int
   */
  public $leadId;

  /**
   * @var string
   *     NOTE: syncStatus should follow the following restrictions
   *     You can have one of the following value
   *     CREATED
   *     UPDATED
   *     FAILED
   */
  public $syncStatus;

  /**
   * @var (object)LeadRecord
   */
  public $leadRecord;

}

class ResultSyncMultipleLeads {

  /**
   * @var (object)ArrayOfSyncStatus
   */
  public $syncStatusList;

}

class StreamPosition {

  /**
   * @var dateTime
   */
  public $latestCreatedAt;

  /**
   * @var dateTime
   */
  public $oldestCreatedAt;

  /**
   * @var dateTime
   */
  public $activityCreatedAt;

  /**
   * @var string
   */
  public $offset;

}

class SyncStatus {

  /**
   * @var int
   */
  public $leadId;

  /**
   * @var string
   *     NOTE: status should follow the following restrictions
   *     You can have one of the following value
   *     CREATED
   *     UPDATED
   *     UNCHANGED
   *     FAILED
   */
  public $status;

  /**
   * @var string
   */
  public $error;

}

class VersionedItem {

  /**
   * @var integer
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  /**
   * @var string
   */
  public $type;

  /**
   * @var string
   */
  public $description;

  /**
   * @var dateTime
   */
  public $timestamp;

}

class ArrayOfActivityRecord {

  /**
   * @var array[0, unbounded] of (object)ActivityRecord
   */
  public $activityRecord;

}

class ArrayOfActivityType {

  /**
   * @var array[0, unbounded] of string
   *     NOTE: activityType should follow the following restrictions
   *     You can have one of the following value
   *     VisitWebpage
   *     FillOutForm
   *     ClickLink
   *     RegisterForEvent
   *     AttendEvent
   *     SendEmail
   *     EmailDelivered
   *     EmailBounced
   *     UnsubscribeEmail
   *     OpenEmail
   *     ClickEmail
   *     NewLead
   *     ChangeDataValue
   *     LeadAssigned
   *     NewSFDCOpprtnty
   *     Wait
   *     RunSubflow
   *     RemoveFromFlow
   *     PushLeadToSales
   *     CreateTask
   *     ConvertLead
   *     ChangeScore
   *     ChangeOwner
   *     AddToList
   *     RemoveFromList
   *     SFDCActivity
   *     EmailBouncedSoft
   *     PushLeadUpdatesToSales
   *     DeleteLeadFromSales
   *     SFDCActivityUpdated
   *     SFDCMergeLeads
   *     MergeLeads
   *     ResolveConflicts
   *     AssocWithOpprtntyInSales
   *     DissocFromOpprtntyInSales
   *     UpdateOpprtntyInSales
   *     DeleteLead
   *     SendAlert
   *     SendSalesEmail
   *     OpenSalesEmail
   *     ClickSalesEmail
   *     AddtoSFDCCampaign
   *     RemoveFromSFDCCampaign
   *     ChangeStatusInSFDCCampaign
   *     ReceiveSalesEmail
   *     InterestingMoment
   *     RequestCampaign
   *     SalesEmailBounced
   */
  public $activityType;

}

class ArrayOfAttribute {

  /**
   * @var array[0, unbounded] of (object)Attribute
   */
  public $attribute;

}

class ArrayOfBase64Binary {
  // You need to set only one from the following two vars

  /**
   * @var array[0, unbounded] of Plain Binary
   */
  public $base64Binary;

  /**
   * @var array[0, unbounded] of base64Binary
   */
  public $base64Binary_encoded;

}

class ArrayOfCampaignRecord {

  /**
   * @var array[0, unbounded] of (object)CampaignRecord
   */
  public $campaignRecord;

}

class ArrayOfLeadChangeRecord {

  /**
   * @var array[0, unbounded] of (object)LeadChangeRecord
   */
  public $leadChangeRecord;

}

class ArrayOfLeadKey {

  /**
   * @var array[0, unbounded] of (object)LeadKey
   */
  public $leadKey;

}

class ArrayOfLeadRecord {

  /**
   * @var array[0, unbounded] of (object)LeadRecord
   */
  public $leadRecord;

}

class ArrayOfLeadStatus {

  /**
   * @var array[0, unbounded] of (object)LeadStatus
   */
  public $leadStatus;

}

class ArrayOfSyncStatus {

  /**
   * @var array[0, unbounded] of (object)SyncStatus
   */
  public $syncStatus;

}

class ArrayOfVersionedItem {

  /**
   * @var array[0, unbounded] of (object)VersionedItem
   */
  public $versionedItem;

}

class paramsGetCampaignsForSource {

  /**
   * @var string
   *     NOTE: source should follow the following restrictions
   *     You can have one of the following value
   *     MKTOWS
   *     SALES
   */
  public $source;

  /**
   * @var string
   */
  public $name;

  /**
   * @var boolean
   */
  public $exactName;

}

class paramsGetLead {

  /**
   * @var (object)LeadKey
   */
  public $leadKey;

}

class paramsGetLeadActivity {

  /**
   * @var (object)LeadKey
   */
  public $leadKey;

  /**
   * @var (object)ActivityTypeFilter
   */
  public $activityFilter;

  /**
   * @var (object)StreamPosition
   */
  public $startPosition;

  /**
   * @var int
   */
  public $batchSize;

}

class paramsGetLeadChanges {

  /**
   * @var (object)StreamPosition
   */
  public $startPosition;

  /**
   * @var (object)ActivityTypeFilter
   */
  public $activityFilter;

  /**
   * @var int
   */
  public $batchSize;

}

class paramsGetMultipleLeads {

  /**
   * @var dateTime
   */
  public $lastUpdatedAt;

  /**
   * @var string
   */
  public $streamPosition;

  /**
   * @var int
   */
  public $batchSize;

}

class paramsListOperation {

  /**
   * @var string
   *     NOTE: listOperation should follow the following restrictions
   *     You can have one of the following value
   *     ADDTOLIST
   *     ISMEMBEROFLIST
   *     REMOVEFROMLIST
   */
  public $listOperation;

  /**
   * @var (object)ListKey
   */
  public $listKey;

  /**
   * @var (object)ArrayOfLeadKey
   */
  public $listMemberList;

  /**
   * @var boolean
   */
  public $strict;

}

class paramsRequestCampaign {

  /**
   * @var string
   *     NOTE: source should follow the following restrictions
   *     You can have one of the following value
   *     MKTOWS
   *     SALES
   */
  public $source;

  /**
   * @var int
   */
  public $campaignId;

  /**
   * @var (object)ArrayOfLeadKey
   */
  public $leadList;

}

class paramsSyncLead {

  /**
   * @var (object)LeadRecord
   */
  public $leadRecord;

  /**
   * @var boolean
   */
  public $returnLead;

  /**
   * @var string
   */
  public $marketoCookie;

}

class paramsSyncMultipleLeads {

  /**
   * @var (object)ArrayOfLeadRecord
   */
  public $leadRecordList;

  /**
   * @var boolean
   */
  public $dedupEnabled;

}

class successGetCampaignsForSource {

  /**
   * @var (object)ResultGetCampaignsForSource
   */
  public $result;

}

class successGetLead {

  /**
   * @var (object)ResultGetLead
   */
  public $result;

}

class successGetLeadActivity {

  /**
   * @var (object)LeadActivityList
   */
  public $leadActivityList;

}

class successGetLeadChanges {

  /**
   * @var (object)ResultGetLeadChanges
   */
  public $result;

}

class successGetMultipleLeads {

  /**
   * @var (object)ResultGetMultipleLeads
   */
  public $result;

}

class successListOperation {

  /**
   * @var (object)ResultListOperation
   */
  public $result;

}

class successRequestCampaign {

  /**
   * @var (object)ResultRequestCampaign
   */
  public $result;

}

class successSyncLead {

  /**
   * @var (object)ResultSyncLead
   */
  public $result;

}

class successSyncMultipleLeads {

  /**
   * @var (object)ResultSyncMultipleLeads
   */
  public $result;

}

// define the class map
class MktowsXmlSchema {

// Do not change the indentation or line breaks below this comment.
// For the curious, it helps with merging new changes from the WSDL code generator.
  static public
      $class_map = array(
    "ActivityRecord" => "ActivityRecord",
    "ActivityTypeFilter" => "ActivityTypeFilter",
    "Attribute" => "Attribute",
    "AuthenticationHeaderInfo" => "AuthenticationHeaderInfo",
    "CampaignRecord" => "CampaignRecord",
    "LeadActivityList" => "LeadActivityList",
    "LeadChangeRecord" => "LeadChangeRecord",
    "LeadKey" => "LeadKey",
    "LeadRecord" => "LeadRecord",
    "LeadStatus" => "LeadStatus",
    "ListKey" => "ListKey",
    "ResultGetCampaignsForSource" => "ResultGetCampaignsForSource",
    "ResultGetLead" => "ResultGetLead",
    "ResultGetLeadChanges" => "ResultGetLeadChanges",
    "ResultGetMultipleLeads" => "ResultGetMultipleLeads",
    "ResultListOperation" => "ResultListOperation",
    "ResultRequestCampaign" => "ResultRequestCampaign",
    "ResultSyncLead" => "ResultSyncLead",
    "ResultSyncMultipleLeads" => "ResultSyncMultipleLeads",
    "StreamPosition" => "StreamPosition",
    "SyncStatus" => "SyncStatus",
    "VersionedItem" => "VersionedItem",
    "ArrayOfActivityRecord" => "ArrayOfActivityRecord",
    "ArrayOfActivityType" => "ArrayOfActivityType",
    "ArrayOfAttribute" => "ArrayOfAttribute",
    "ArrayOfBase64Binary" => "ArrayOfBase64Binary",
    "ArrayOfCampaignRecord" => "ArrayOfCampaignRecord",
    "ArrayOfLeadChangeRecord" => "ArrayOfLeadChangeRecord",
    "ArrayOfLeadKey" => "ArrayOfLeadKey",
    "ArrayOfLeadRecord" => "ArrayOfLeadRecord",
    "ArrayOfLeadStatus" => "ArrayOfLeadStatus",
    "ArrayOfSyncStatus" => "ArrayOfSyncStatus",
    "ArrayOfVersionedItem" => "ArrayOfVersionedItem",
    "paramsGetCampaignsForSource" => "paramsGetCampaignsForSource",
    "paramsGetLead" => "paramsGetLead",
    "paramsGetLeadActivity" => "paramsGetLeadActivity",
    "paramsGetLeadChanges" => "paramsGetLeadChanges",
    "paramsGetMultipleLeads" => "paramsGetMultipleLeads",
    "paramsListOperation" => "paramsListOperation",
    "paramsRequestCampaign" => "paramsRequestCampaign",
    "paramsSyncLead" => "paramsSyncLead",
    "paramsSyncMultipleLeads" => "paramsSyncMultipleLeads",
    "successGetCampaignsForSource" => "successGetCampaignsForSource",
    "successGetLead" => "successGetLead",
    "successGetLeadActivity" => "successGetLeadActivity",
    "successGetLeadChanges" => "successGetLeadChanges",
    "successGetMultipleLeads" => "successGetMultipleLeads",
    "successListOperation" => "successListOperation",
    "successRequestCampaign" => "successRequestCampaign",
    "successSyncLead" => "successSyncLead",
    "successSyncMultipleLeads" => "successSyncMultipleLeads");

}

/**
 * Client for Marketo SOAP API.
 *
 */
class marketoClient {
  // Change this vale to true to enable debug output.

  const DEBUG = false;
  const CLIENT_TZ = 'America/Los_Angeles';
  const MKTOWS_USER_ID = 'savvisdirect1_107382055016A9B55ECA07';
  const MKTOWS_SECRET_KEY = '118786958836475855001166AA99BB6755FF141EFE45';
  const MKTOWS_NAMESPACE = 'http://www.marketo.com/mktows/';

  protected $accessKey;
  protected $secretKey;

  /**
   * @var SoapClient
   */
  protected $soapClient;

  public function __construct($accessKey, $secretKey, $soapEndPoint) {
    $this->accessKey = $accessKey;
    $this->secretKey = $secretKey;

    $options = array("connection_timeout" => 20, "location" => $soapEndPoint);
    if (self::DEBUG) {
      $options["trace"] = true;
    }

    $wsdlUri = $soapEndPoint . '?WSDL';

    $this->soapClient = new SoapClient($wsdlUri, $options);
  }

  private function _getAuthenticationHeader() {
    $dtzObj = new DateTimeZone(self::CLIENT_TZ);
    $dtObj = new DateTime('now', $dtzObj);
    $timestamp = $dtObj->format(DATE_W3C);
    //$timestamp = '2009-01-27T15:53';

    $encryptString = $timestamp . $this->accessKey;

    $signature = hash_hmac('sha1', $encryptString, $this->secretKey);
    //echo "encrypt:   $encryptString\n";
    //echo "key:       {this->secretKey}\n";
    //echo "signature: $signature\n";

    $attrs = new stdClass();
    $attrs->mktowsUserId = $this->accessKey;
    $attrs->requestSignature = $signature;
    $attrs->requestTimestamp = $timestamp;

    $soapHdr = new SoapHeader(self::MKTOWS_NAMESPACE, 'AuthenticationHeader', $attrs);
    return $soapHdr;
  }

  public function getFields() {
    $options = array();
    $params = array(
      'objectName' => 'LeadRecord'
    );

    $authHdr = $this->_getAuthenticationHeader();

    $fieldset = array();

    try {
      $result = $this->soapClient->__soapCall('describeMObject', array($params), $options, $authHdr);

      foreach ($result->result->metadata->fieldList->field as $field) {
        $fieldset[$field->name] = $field->displayName;
      }
    } catch (Exception $e) {
      // TODO: How should this be handled?
    }

    natcasesort($fieldset);

    return $fieldset;
  }

  public function getLead($keyType, $keyValue) {
    $retLead = null;

    $leadKey = new LeadKey();
    $leadKey->keyType = $keyType;
    $leadKey->keyValue = $keyValue;

    $params = new paramsGetLead();
    $params->leadKey = $leadKey;

    $options = array();

    $authHdr = $this->_getAuthenticationHeader();

    try {
      $success = $this->soapClient->__soapCall('getLead', array($params), $options, $authHdr);

      if (self::DEBUG) {
        $req = $this->soapClient->__getLastRequest();
        echo "RAW request:\n$req\n";
        $resp = $this->soapClient->__getLastResponse();
        echo "RAW response:\n$resp\n";
      }

      if (isset($success->result)) {
        if ($success->result->count > 1) {
          // Is this okay?  If not, raise exception
        }
        if (isset($success->result->leadRecordList->leadRecord)) {
          $leadRecList = $success->result->leadRecordList->leadRecord;
          if (!is_array($leadRecList)) {
            $leadRecList = array($leadRecList);
            $count = count($leadRecList);
            if ($count > 0) {
              $retLead = $leadRecList[$count - 1];
            }
          }
        }
      }
    } catch (SoapFault $ex) {
      $ok = false;
      $errCode = 1;
      $faultCode == null;
      if (!empty($ex->detail->serviceException->code)) {
        $errCode = $ex->detail->serviceException->code;
      }
      if (!empty($ex->faultCode)) {
        $faultCode = $ex->faultCode;
      }
      switch ($errCode) {
        case mktWsError::ERR_LEAD_NOT_FOUND:
          $ok = true;
          $success = false;
          break;
        default:
      }
      if (!$ok) {
        if ($faultCode != null) {
          if (strpos($faultCode, 'Client')) {
            // This is a client error.  Check the other codes and handle.
          }
          else if (strpos($faultCode, 'Server')) {
            // This is a server error.  Call Marketo support with details.
          }
          else {
            // W3C spec has changed :)
            // But seriously, Call Marketo support with details.
          }
        }
        else {
          // Not a good place to be.
        }
      }
    } catch (Exception $ex) {
      $msg = $ex->getMessage();
      $req = $this->soapClient->__getLastRequest();
      echo "Error occurred for request: $msg\n$req\n";
      var_dump($ex);
      exit(1);
    }

    return $retLead;
  }

  public function syncLead($key, $attrs) {
    // Build array of Attribute objects
    $attrArray = array();
    foreach ($attrs as $attrName => $attrValue) {
      $a = new Attribute();
      $a->attrName = $attrName;
      $a->attrValue = $attrValue;
      $attrArray[] = $a;
    }

    $aryOfAttr = new ArrayOfAttribute();
    $aryOfAttr->attribute = $attrArray;

    // Build LeadRecord
    $leadRec = new LeadRecord();
    $leadRec->leadAttributeList = $aryOfAttr;

    // Set the unique lead key.
    if (is_numeric($key)) {
      $leadRec->Id = $key;  // Marketo system ID.
    }
    else {
      $leadRec->Email = $key; // TODO - Add email format validation - should be SMTP email address.
    }

    // Build API params
    $params = new paramsSyncLead();
    $params->leadRecord = $leadRec;

    $params->returnLead = false;  // Don't return the full lead record - just the ID.

    $options = array();

    $authHdr = $this->_getAuthenticationHeader();

    try {
      $success = $this->soapClient->__soapCall('syncLead', array($params), $options, $authHdr);
      $resp = $this->soapClient->__getLastResponse();

      if (self::DEBUG) {
        $req = $this->soapClient->__getLastRequest();
        echo "RAW request:\n$req\n";
        $resp = $this->soapClient->__getLastResponse();
        echo "RAW response:\n$resp\n";
      }
    } catch (SoapFault $ex) {
      $ok = false;
      $errCode = 1;
      $faultCode == null;
      if (!empty($ex->detail->serviceException->code)) {
        $errCode = $ex->detail->serviceException->code;
      }
      if (!empty($ex->faultCode)) {
        $faultCode = $ex->faultCode;
      }
      switch ($errCode) {
        case mktWsError::ERR_LEAD_SYNC_FAILED:
          // Retry once and handle error if retry fails.
          break;
        default:
      }
      if (!$ok) {
        if ($faultCode != null) {
          if (strpos($faultCode, 'Client')) {
            // This is a client error.  Check the other codes and handle.
          }
          else if (strpos($faultCode, 'Server')) {
            // This is a server error.  Call Marketo support with details.
          }
          else {
            // W3C spec has changed :)
            // But seriously, Call Marketo support with details.
          }
        }
        else {
          // Not a good place to be.
        }
      }
    } catch (Exception $ex) {
      $msg = $ex->getMessage();
      $req = $this->soapClient->__getLastRequest();
      echo "Error occurred for request: $msg\n$req\n";
      var_dump($ex);
      exit(1);
    }

    return $success;
  }

  public function getCampaignsForSource() {
    $retList = null;

    $params = new paramsGetCampaignsForSource();
    $params->source = 'MKTOWS';  // We want campaigns configured for access through the SOAP API

    $authHdr = $this->_getAuthenticationHeader();

    try {
      $success = $this->soapClient->__soapCall('getCampaignsForSource', array($params), $options, $authHdr);

      if (self::DEBUG) {
        $req = $this->soapClient->__getLastRequest();
        echo "RAW request:\n$req\n";
        $resp = $this->soapClient->__getLastResponse();
        echo "RAW response:\n$resp\n";
      }

      if (isset($success->result->returnCount) && $success->result->returnCount > 0) {
        if (isset($success->result->campaignRecordList->campaignRecord)) {
          $retList = array();
          // campaignRecordList is ArrayOfCampaignRecord from WSDL
          $campRecList = $success->result->campaignRecordList->campaignRecord;
          // Force to array when one 1 item is returned (quirk of PHP SOAP)
          if (!is_array($campRecList)) {
            $campRecList = array($campRecList);
          }
          // $campRec is CampaignRecord from WSDL
          foreach ($campRecList as $campRec) {
            $retList[$campRec->name] = $campRec->id;
          }
        }
      }
    } catch (SoapFault $ex) {
      if (self::DEBUG) {
        $req = $this->soapClient->__getLastRequest();
        echo "RAW request:\n$req\n";
        $resp = $this->soapClient->__getLastResponse();
        echo "RAW response:\n$resp\n";
      }
      $ok = false;
      $errCode = !empty($ex->detail->serviceException->code) ? $ex->detail->serviceException->code : 1;
      $faultCode = !empty($ex->faultCode) ? $ex->faultCode : null;
      switch ($errCode) {
        case mktWsError::ERR_CAMP_NOT_FOUND:
          // Handle error for campaign not found
          break;
        default:
        // Handle other errors
      }
      if (!$ok) {
        if ($faultCode != null) {
          if (strpos($faultCode, 'Client')) {
            // This is a client error.  Check the other codes and handle.
          }
          else if (strpos($faultCode, 'Server')) {
            // This is a server error.  Call Marketo support with details.
          }
          else {
            // W3C spec has changed :)
            // But seriously, Call Marketo support with details.
          }
        }
        else {
          // Not a good place to be.
        }
      }
    } catch (Exception $ex) {
      $msg = $ex->getMessage();
      $req = $this->soapClient->__getLastRequest();
      echo "Error occurred for request: $msg\n$req\n";
      var_dump($ex);
      exit(1);
    }

    return $retList;
  }

  public function requestCampaign($campId, $leadEmail) {
    $retStat = false;

    $leadKey = new LeadKey();
    $leadKey->keyType = 'IDNUM';
    $leadKey->keyValue = $leadEmail;

    $leadList = new ArrayOfLeadKey();
    $leadList->leadKey = array($leadKey);

    $params = new paramsRequestCampaign();
    $params->campaignId = $campId;
    $params->leadList = $leadList;
    $params->source = 'MKTOWS';

    $authHdr = $this->_getAuthenticationHeader();

    try {
      $success = $this->soapClient->__soapCall('requestCampaign', array($params), $options, $authHdr);

      if (self::DEBUG) {
        $req = $this->soapClient->__getLastRequest();
        echo "RAW request:\n$req\n";
        $resp = $this->soapClient->__getLastResponse();
        echo "RAW response:\n$resp\n";
      }

      if (isset($success->result->success)) {
        $retStat = $success->result->success;
      }
    } catch (SoapFault $ex) {
      $ok = false;
      $errCode = !empty($ex->detail->serviceException->code) ? $ex->detail->serviceException->code : 1;
      $faultCode = !empty($ex->faultCode) ? $ex->faultCode : null;
      switch ($errCode) {
        case mktWsError::ERR_LEAD_NOT_FOUND:
          // Handle error for campaign not found
          break;
        default:
        // Handle other errors
      }
      if (!$ok) {
        if ($faultCode != null) {
          if (strpos($faultCode, 'Client')) {
            // This is a client error.  Check the other codes and handle.
          }
          else if (strpos($faultCode, 'Server')) {
            // This is a server error.  Call Marketo support with details.
          }
          else {
            // W3C spec has changed :)
            // But seriously, Call Marketo support with details.
          }
        }
        else {
          // Not a good place to be.
        }
      }
    } catch (Exception $ex) {
      $msg = $ex->getMessage();
      $req = $this->soapClient->__getLastRequest();
      echo "Error occurred for request: $msg\n$req\n";
      var_dump($ex);
      exit(1);
    }

    return $retStat;
  }

  /**
   * Enter description here...
   *
   * @param string $leadEmail       Lead email
   * @param string $listName        Name of static list
   * @param string $sinceTimestamp  Some valid PHP time string like 2009-12-25 01:00:00
   * @param int    $lastId          ID of last activity 
   */
  public function wasLeadAddedToListSince($leadId, $listName, $sinceTimestamp, $lastId) {
    $wasAdded = false;
    $actRec = null;

    $leadKey = new LeadKey();
    $leadKey->keyType = 'IDNUM';
    $leadKey->keyValue = $leadId;
    $params = new paramsGetLeadActivity();
    $params->leadKey = $leadKey;

    $actTypes = array();
    $actTypes[] = 'AddToList';
    $actArray = new ArrayOfActivityType();
    $actArray->activityType = $actTypes;
    $filter = new ActivityTypeFilter();
    $filter->includeTypes = $actArray;
    $params->activityFilter = $filter;

    $startPos = new StreamPosition();
    $dtzObj = new DateTimeZone(self::CLIENT_TZ);  // Use the correct time zone !
    $dtObj = new DateTime($sinceTimestamp, $dtzObj);
    $startPos->oldestCreatedAt = $dtObj->format(DATE_W3C);
    $params->startPosition = $startPos;

    $params->batchSize = 100;

    $doPage = true;
    while ($doPage) {
      $authHdr = $this->_getAuthenticationHeader();

      try {
        $success = $this->soapClient->__soapCall('getLeadActivity', array($params), $options, $authHdr);

        if (self::DEBUG) {
          $req = $this->soapClient->__getLastRequest();
          echo "RAW request:\n$req\n";
          $resp = $this->soapClient->__getLastResponse();
          echo "RAW response:\n$resp\n";
        }

        if (isset($success->leadActivityList)) {
          // leadActivityList is LeadActivityList in WSDL
          $result = $success->leadActivityList;
          if ($result->returnCount > 0) {
            // actRecList is ArrayOfActivityRecord from WSDL
            $actRecList = $result->activityRecordList;
            // Force to array when one 1 item is returned (quirk of PHP SOAP)
            if (!is_array($actRecList)) {
              $actRecList = array($actRecList);
            }
            foreach ($actRecList as $actRec) {
              if ($actRec->id > $lastId && $actRec->mktgAssetName == $listName) {
                $wasAdded = true;
                break 2;
              }
            }
            $newStartPos = $success->leadActivityList->newStartPosition;
            $params->startPosition = $newStartPos;
          }
          else {
            $doPage = false;
          }
        }
      } catch (SoapFault $ex) {
        $doPage = false;
        $ok = false;
        $errCode = !empty($ex->detail->serviceException->code) ? $ex->detail->serviceException->code : 1;
        $faultCode = !empty($ex->faultCode) ? $ex->faultCode : null;
        switch ($errCode) {
          case mktWsError::ERR_LEAD_NOT_FOUND:
            // Handle error for lead not found
            break;
          default:
          // Handle other errors
        }
        if (!$ok) {
          if ($faultCode != null) {
            if (strpos($faultCode, 'Client')) {
              // This is a client error.  Check the other codes and handle.
            }
            else if (strpos($faultCode, 'Server')) {
              // This is a server error.  Call Marketo support with details.
            }
            else {
              // W3C spec has changed :)
              // But seriously, Call Marketo support with details.
            }
          }
          else {
            // Not a good place to be.
          }
        }
        break;
      } catch (Exception $ex) {
        $msg = $ex->getMessage();
        $req = $this->soapClient->__getLastRequest();
        echo "Error occurred for request: $msg\n$req\n";
        var_dump($ex);
        exit(1);
      }
    }

    return array($wasAdded, $actRec);
  }

}

class mktWsError {

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
