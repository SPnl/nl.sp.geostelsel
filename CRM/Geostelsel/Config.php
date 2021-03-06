<?php

class CRM_Geostelsel_Config {

  protected static $_singleton;
  
  protected $regio_rel_type_id = false; //relatie type tussen afdeling en regio
  
  protected $provincie_rel_type_id = false; //relatie type tussen regio en provincie
  
  protected $afdelings_gemeente_group; //custom field set for gemeentes at afdeling
  
  protected $afdelings_gemeente_field; //custom field for gemeente at afdeling
  
  protected $geostelsel_custom_group; //custom field for geostelsel group
  
  protected $geostelsel_handmatige_invoer; //custom field for handmatige group
  
  protected $geostelsel_afdeling; //custom field for afdeling 
  
  protected $geostelsel_regio; //custom field for regio
  
  protected $geostelsel_provincie; //custom field for provincie
  
  protected $postcode_custom_group; //custom field for postcode
  
  protected $postcode_gemeeente_field; //gemeente field on postcode custom set
  
  protected $permission_table; 
  
  protected $permission_field;

  protected function __construct() {

    $cfsp = CRM_Spgeneric_CustomField::singleton();

    //custom group from org.civicoop.postcodenl
    $this->postcode_custom_group = $cfsp->getGroupByName('Adresgegevens');
    $this->postcode_gemeeente_field = $cfsp->getField('Adresgegevens', 'Gemeente');
    
    $this->permission_table = $cfsp->getGroupByName('Toegangsgegevens');
    $this->permission_field = $cfsp->getField('Toegangsgegevens', 'Toegang_tot_contacten_van');
    
    $this->geostelsel_custom_group = $cfsp->getGroupByName('Geostelsel');
    $this->geostelsel_handmatige_invoer = $cfsp->getField('Geostelsel', 'Handmatige_invoer');
    $this->geostelsel_afdeling = $cfsp->getField('Geostelsel', 'Afdeling');
    $this->geostelsel_regio = $cfsp->getField('Geostelsel', 'Regio');
    $this->geostelsel_provincie = $cfsp->getField('Geostelsel', 'Provincie');
    
    $this->afdelings_gemeente_group = $cfsp->getGroupByName('Gemeentes');
    $this->afdelings_gemeente_field = $cfsp->getField('Gemeentes', 'Gemeente');
    
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
  
  public function getGeostelselCustomGroup($key='id') {
    return $this->geostelsel_custom_group[$key];
  }
  
  public function getHandmatigeInvoerField($key='id') {
    return $this->geostelsel_handmatige_invoer[$key];
  }
  
  public function getAfdelingsField($key='id') {
    return $this->geostelsel_afdeling[$key];
  }
  
  public function getRegioField($key='id') {
    return $this->geostelsel_regio[$key];
  }
  
  public function getProvincieField($key='id') {
    return $this->geostelsel_provincie[$key];
  }
  
  public function getPostcodeCustomGroup($key='id') {
    return $this->postcode_custom_group[$key];
  }
  
  public function getPostcodeGemeenteCustomField($key='id') {
    return $this->postcode_gemeeente_field[$key];
  }
  
  public function getPermissionTable($key='id') {
    return $this->permission_table[$key];
  }
  
  public function getPermissionField($key='id') {
    return $this->permission_field[$key];
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
