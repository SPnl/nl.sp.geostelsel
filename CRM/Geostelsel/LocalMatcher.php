<?php

/* 
 * This class find target ID's for the automatic relationship based on the custom field gemeente
 */

class CRM_Geostelsel_LocalMatcher extends CRM_Autorelationship_Matcher {
  
  protected $objRelationship;
  
  /**
   * The ID of the custom field group 'Automatic Relationship'
   * 
   * @var int
   */
  protected $autogroup_id;
  
  /**
   * The ID of the relationship ID field on relationship, which is a custom field
   * 
   * @var int
   */
  protected $relationshipfield_id;
  
  protected static $match_relationship_types = false;
  
  protected static $local_regio_relationship_type_id = false;
  
  
  /**
   * 
   * @param $objAddress
   */
  public function __construct(CRM_Autorelationship_TargetInterface $interface) {
    parent::__construct($interface);    
    $this->autogroup_id = $this->getCustomGroupIdByName('autorelationship_local_based');
    $this->relationshipfield_id = $this->getCustomFieldIdByNameAndGroup('Relationship_ID', $this->autogroup_id);
  }
  
  public static function getMatchRelationshipTypeIds() {
    if (self::$match_relationship_types === false) {
      self::$match_relationship_types = array();
      try {
        $params['name_a_b'] = 'gemeente_based';
        $relationship = civicrm_api3('RelationshipType', 'getsingle', $params);
        self::$match_relationship_types[] = $relationship['id'];
      } catch (Exception $ex) {

      }
    }
    return self::$match_relationship_types;
  }
  
  public static function getLocalRegioRelationshipTypeId() {
    if (self::$local_regio_relationship_type_id === false) {
      try {
        $params['name_a_b'] = 'local_regio';
        $relationship = civicrm_api3('RelationshipType', 'getsingle', $params);
        self::$local_regio_relationship_type_id = $relationship['id'];
      } catch (Exception $ex) {

      }
    }
    return self::$local_regio_relationship_type_id;
  }
  
  public function getRelationshipTypeNameAB() {
    return 'local_based';
  }
  
  public function setData($data) {
    parent::setData($data);
    if (isset($this->data['relationship'])) {
      $this->objRelationship = $this->data['relationship'];
    }
  }
  
  /**
   * Returns an array with the contact IDs which should have a relationship to the contact based on the rule settings
   * array is build as follows:
   * [] = array (
   *  'contactId' => $contactId,
   *  'entity_id' => //Id of the target rule entity in the database
   *  'entity' => //System name of the entity
   * )
   * 
   * The 'entity_id' is the id of the rule in the database
   * 
   * @return array
   */
  public function findTargetContactIds() {
    if (!isset($this->objRelationship)) {
      throw new Exception('Relationship not set');
    }    
    
    $matchTypes = self::getMatchRelationshipTypeIds();
    if (!in_array($this->objRelationship->relationship_type_id, $matchTypes)) {
      return array();
    }
    
    $checkRelationTypeId = self::getLocalRegioRelationshipTypeId();
    if ($checkRelationTypeId === false) {
      return array();
    }
    
    $sql = "SELECT `r`.`contact_id_b` AS `contact_id`, `rule`.`id` AS `id` FROM `civicrm_relationship` `r` "
        . " INNER JOIN `civicrm_autorelationship_local_member` `rule` ON `rule`.`contact_id` = `r`.`contact_id_b` "
        . " WHERE `r`.`relationship_type_id` = %1 AND `r`.`contact_id_a` = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      '1' => array($checkRelationTypeId, 'Integer'),
      '2' => array($this->objRelationship->contact_id_b, 'Integer'),
    ));
    
    $return = array();
    while($dao->fetch()) {
      $target['contact_id'] = $dao->contact_id;
      $target['entity_id'] = $dao->id;
      $target['entity'] = $this->interface->getEntitySystemName();
      
      $return[] = $target;
    }
    
    return array_unique($return);
  }
  
  /**
   * Returns an array with all the contacts which should have a relationship based on the tule rule $entity_id
   * array is build as follows:
   * [] = array (
   *  'contactId' => $contactId //the source contactId 
   *  'entity_id' => //Id of the target rule entity in the database
   *  'entity' => //System name of the entity
   * )
   * 
   * @param $entity_id the ID of the rule in the database
   * @return array
   */
  public function findSourceContactIds($entity_id) {
    //retrieve the city value of the rule
    $sql = "SELECT * FROM `civicrm_autorelationship_local_member` WHERE `id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array('1' => array($entity_id, 'Integer')));
    $cid = false;
    if ($dao->fetch()) {
      $cid = $dao->contact_id;
    }
    
    if ($cid === false) {
      return array();
    }
    
    $checkRelationTypeId = self::getLocalRegioRelationshipTypeId();
    if ($checkRelationTypeId === false) {
      return array();
    }
    
    $matchTypes = self::getMatchRelationshipTypeIds();
    if (!count($matchTypes)) {
      return array();
    }
    
    //find all related local departments
    $sqlLocalDepartments = "SELECT * FROM `civicrm_relationship` WHERE `relationship_type_id` = %1 AND `contact_id_b` = %2";
    $localDepartment = CRM_Core_DAO::executeQuery($sqlLocalDepartments, array(
      '1' => array($checkRelationTypeId, "Integer"),
      '2' => array($cid, "Integer"),
    ));
    //echo $checkRelationTypeId; echo " - "; echo $cid; echo " - "; echo $sqlLocalDepartments;exit();
    $return = array();
    while($localDepartment->fetch()) {
      //for every local department find their members
      $member_sql = "SELECT * FROM `civicrm_relationship` WHERE `relationship_type_id` IN (".implode(",", $matchTypes).") AND `contact_id_b` = %1";
      $relDAO = CRM_Core_DAO::executeQuery($member_sql, array(
        '1' => array($localDepartment->contact_id_a, 'Integer'),
      ), TRUE, 'CRM_Contact_DAO_Relationship');
      while($relDAO->fetch()) {
        $target['contact_id'] = $relDAO->contact_id_a;
        $target['entity_id'] = $entity_id;
        $target['entity'] = $this->interface->getEntitySystemName();
        
        $dataArray = $dao->toArray();
        $dataObject = json_decode(json_encode($dataArray), FALSE); //we need an object for the target data parameter
        
        $target['data']['relationship'] = $dataObject;
      
        $return[] = $target;
      }
    }
    return $return;
  }
  
  /**
   * Update the relationship parameters. E.g. for setting a custom field
   * 
   * @param type $arrRelationshipParams
   * @param array $target = array ( 'contact_id' => id, 'entity_id' => int, 'entity' => string)
   */
  public function updateRelationshipParameters(&$arrRelationshipParams, $target) {
    parent::updateRelationshipParameters($arrRelationshipParams, $target);
    
    if (!isset($this->objRelationship)) {
      throw new Exception('Relationship not set');
    }  
    $arrRelationshipParams['custom_'.$this->relationshipfield_id] = $this->objRelationship->id;
    $arrRelationshipParams['is_permission_b_a'] = '1';
    
    /*$arrRelationshipParams['start_date'] = "";
    if (!empty($this->objRelationship->start_date) && strtoupper($this->objRelationship->start_date) != "NULL") {
      $startDate = new DateTime($this->objRelationship->start_date);
      $arrRelationshipParams['start_date'] = $startDate->format('YmdHis');
    }
    $arrRelationshipParams['end_date'] = "";
    if (!empty($this->objRelationship->end_date) && strtoupper($this->objRelationship->end_date) != "NULL") {
      $endDate = new DateTime($this->objRelationship->end_date);
      $arrRelationshipParams['end_date'] = $endDate->format('YmdHis');
    }
    $arrRelationshipParams['is_active'] = 1;
    if (empty($this->objRelationship->is_active)) {
      $arrRelationshipParams['is_active'] = 0;
    }*/
  }
  
  /**
   * Returns the contact ID for on the A side of the relationship
   * 
   * @return int the contact ID for the A side of the relationship
   */
  public function getContactId() {
    if (!isset($this->objRelationship) || !isset($this->objRelationship->contact_id_a)) {
      throw new Exception('Relationship not set');
    } 
    return $this->objRelationship->contact_id_a;
  }
  
}


