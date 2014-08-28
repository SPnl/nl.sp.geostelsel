<?php

class CRM_Geostelsel_GeoInfo_Repository {

  protected static $_singleton;
  
  protected $_cache;
  
  protected $afdelings_gemeente_group;
  
  protected $afdelings_gemeente_field;
  

  protected function __construct() {
    $this->_cache = array();
    $this->afdelings_gemeente_group = civcirm_api3('CustomGroup', 'getsingle', array('name' => 'Gemeentes'));
    $this->afdelings_gemeente_field = civcirm_api3('CustomField', 'getsingle', array('name' => 'Gemeente', 'custom_group_id' => $this->afdelings_gemeente_group['id']));
  }
  
  /**
   * @return CRM_Geostelsel_GeoInfo_Repository
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Geostelsel_GeoInfo_Repository();
    }
    return self::$_singleton;
  }
  
  public function getGeoInfoByGemeente($gemeente) {
    if (!isset($this->_cache[$gemeente])) {
      $afdelings_contact_id = $this->getAfdelingByGemeente($gemeente);
      $this->_cache[$gemeente] = new CRM_Geostelsel_GeoInfo_Data($afdelings_contact_id);
      
    }
    return $this->_cache[$gemeente];
  }
  
  protected function getAfdelingByGemeente($gemeente) {
    $sql = "SELECT * FROM `".$this->afdelings_gemeente_group['table_name']."` WHERE `".$this->afdelings_gemeente_field['column_name']."` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($gemeente, 'String')));
    if ($dao->fetch()) {
      return $dao->entity_id;
    }
    return false;
  }

}
