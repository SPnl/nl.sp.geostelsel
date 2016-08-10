<?php

class CRM_Geostelsel_Groep_Config {
  
  protected static $_singleton;
  
  protected $groep_customgroup;
  
  protected $groep_customfield;
  
  protected $groep_option_group;
  
  protected function __construct() {
    $this->groep_option_group = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'afdeling_groep'));

    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $this->groep_customgroup = $cfsp->getGroupByName('afdeling_groep');
    $this->groep_customfield = $cfsp->getField('afdeling_groep', 'afdeling_groep');
  }
  
  /**
   * 
   * @return CRM_Geostelsel_Groep_Config
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Geostelsel_Groep_Config();
    }
    return self::$_singleton;
  }
  
  public function getGroepOptionGroup($key='id') {
    return $this->groep_option_group[$key];
  }
  
  public function getGroepCustomGroup($key='id') {
    return $this->groep_customgroup[$key];
  }
  
  public function getGroepCustomField($key='id') {
    return $this->groep_customfield[$key];
  }
      
  
  
}

