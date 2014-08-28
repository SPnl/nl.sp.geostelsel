<?php

class CRM_Geostelsel_GeoInfo_Repository {

  protected static $_singleton;
  
  protected $_cache;
  
  protected function __construct() {
    $this->_cache = array();
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
  
  /**
   * 
   * @param type $gemeente
   * @return CRM_Geostelsel_GeoInfo_Data
   */
  public function getGeoInfoByGemeente($gemeente) {
    if (!isset($this->_cache[$gemeente])) {
      $afdelings_contact_id = $this->getAfdelingByGemeente($gemeente);
      $this->_cache[$gemeente] = new CRM_Geostelsel_GeoInfo_Data($afdelings_contact_id);
      
    }
    return $this->_cache[$gemeente];
  }
  
  protected function getAfdelingByGemeente($gemeente) {
    $config = CRM_Geostelsel_Config::singleton();
    $table = $config->getGemeenteCustomGroup('table_name');
    $field = $config->getGemeenteCustomField('column_name');
    $sql = "SELECT * FROM `".$table."` WHERE `".$field."` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($gemeente, 'String')));
    if ($dao->fetch()) {
      return $dao->entity_id;
    }
    return false;
  }

}
