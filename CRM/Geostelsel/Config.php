<?php

class CRM_Geostelsel_Config {

  protected static $_singleton;
  
  protected $regio_rel_type_id = false; //relatie type tussen afdeling en regio
  
  protected $provincie_rel_type_id = false; //relatie type tussen regio en provincie
  
  protected $afdelings_gemeente_group; //custom field set for gemeentes at afdeling
  
  protected $afdelings_gemeente_field; //custom field for gemeente at afdeling

  protected function __construct() {
    $this->afdelings_gemeente_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Gemeentes'));
    $this->afdelings_gemeente_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'Gemeente', 'custom_group_id' => $this->afdelings_gemeente_group['id']));
    $this->regio_rel_type_id = $this->getRelationshipTypeIdByNameAB('sprel_afdeling_regio');
    $this->provincie_rel_type_id = $this->getRelationshipTypeIdByNameAB('sprel_regio_provincie');
  }

  /**
   * @return CRM_Geostelsel_Config
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Geostelsel_Config();
    }
    return self::$_singleton;
  }
  
  public function getRegioRelationshipTypeId() {
    return $this->regio_rel_type_id;
  }
  
  public function getProvincieRelationshipTypeId() {
    return $this->provincie_rel_type_id;
  }
  
  public function getGemeenteCustomField($key='id') {
    return $this->afdelings_gemeente_field[$key];
  }
  
  public function getGemeenteCustomGroup($key='id') {
    return $this->afdelings_gemeente_group[$key];
  }

  protected function getRelationshipTypeIdByNameAB($name_a_b) {
    try {
      $result = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => $name_a_b));
      return $result['id'];
    } catch (Exception $ex) {
      
    }
    return false;
  }

}
