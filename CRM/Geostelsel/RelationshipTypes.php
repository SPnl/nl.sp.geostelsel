<?php

/* 
 * This file is to retrieve relationship types for the geo stelsel
 * 
 */

class CRM_Geostelsel_RelationshipTypes {
  
  protected static $instance;
  
  protected $kaderfuncties_rel_type_ids = array();
  
  protected $regio_rel_type_ids = array();
  
  protected $lokaal_lid_rel_type_ids = array();
  
  
  protected function __construct() {
    $this->getRelationshipTypeIdByNameAB('kaderfunctie_ab', $this->kaderfuncties_rel_type_ids);
    $this->getRelationshipTypeIdByNameAB('sprel_afdeling_regio', $this->regio_rel_type_ids);
    $this->getRelationshipTypeIdByNameAB('gemeente_based_ab', $this->lokaal_lid_rel_type_ids);
  }
  
  /**
   * Obtain a refernece to the active upgrade handler
   */
  static public function singleton() {
    if (! self::$instance) {
      self::$instance = new CRM_Geostelsel_RelationshipTypes();
    }
    return self::$instance;
  }
  
  public function getKaderfunctieRelationshipTypeIds() {
    return $this->kaderfuncties_rel_type_ids;
  }
  
  public function getRegioRelationshipTypeIds() {
    return $this->regio_rel_type_ids;
  }
  
  public function getLokaalLidRelationshipTypeIds() {
    return $this->lokaal_lid_rel_type_ids;
  }
  
  protected function getRelationshipTypeIdByNameAB($name_a_b, &$addTo) {
    try {
      $result = civicrm_api3('RelationshipType', 'getsingle', array('name_a_b' => $name_a_b));
      $addTo[] = $result['id'];
    } catch (Exception $ex) {
      
    }
  }
}

