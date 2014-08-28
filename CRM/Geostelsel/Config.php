<?php

class CRM_Geostelsel_Config {

  protected static $_singleton;
  
  protected $regio_rel_type_id = false; //relatie type tussen afdeling en regio
  
  protected $provincie_rel_type_id = false; //relatie type tussen regio en provincie

  protected function __construct() {
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

  protected function getRelationshipTypeIdByNameAB($name_a_b, &$addTo) {
    try {
      $result = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => $name_a_b));
      return $result['id'];
    } catch (Exception $ex) {
      
    }
    return false;
  }

}
