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
      if ($afdelings_contact_id) { 
        $this->_cache[$gemeente] = new CRM_Geostelsel_GeoInfo_Data($afdelings_contact_id);
      } else {
        $this->_cache[$gemeente] = false;
      }
      
    }
    return $this->_cache[$gemeente];
  }
  
  public function updateContact($contact_id, $gemeente) {
    $config = CRM_Geostelsel_Config::singleton();
  
    $table = $config->getGeostelselCustomGroup('table_name');
    $afdeling = $config->getAfdelingsField('column_name');
    $regio = $config->getRegioField('column_name');
    $provincie = $config->getProvincieField('column_name');
    $manual = $config->getHandmatigeInvoerField('column_name');
    
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `{$table}` WHERE `entity_id` = %1", array(1 => array($contact_id, 'Integer')));
    $existing = false;
    if ($dao->fetch()) {
      if ($dao->$manual) {
        return;
      } else {
        $existing = $dao->id;
      }
    }
    
    $data = $this->getGeoInfoByGemeente($gemeente);
    if ($data === false) {
      $data = new CRM_Geostelsel_GeoInfo_Data(0);
    }
    
    $params[$afdeling] = $data->getAfdelingsContactId() ? $data->getAfdelingsContactId() : 'NULL';
    $params[$regio] = $data->getRegioContactId() ? $data->getRegioContactId() : 'NULL';
    $params[$provincie] = $data->getProvincieContactId() ? $data->getProvincieContactId() : 'NULL';
    
    if ($existing) {
      $sql = "UPDATE {$table} SET ";
      $i =0;
      foreach($params as $key => $val) {
        if ($i > 0) {
          $sql .= ",";
        }
        $sql .= " `".$key."` = ".$val;
        $i++;
      }
      $sql .= " WHERE `id` = %1";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($existing, 'Integer'),
      ));
    } else {
      $sql = "INSERT INTO {$table} (`entity_id`, `{$manual}`";
      foreach($params as $key => $val) {
         $sql .= ", `".$key."`";
      }
      $sql .= ") VALUES (%1, 0";
      foreach($params as $key => $val) {
         $sql .= ", ".$val."";
      }
      $sql .= ")";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($contact_id, 'Integer'),
      ));
    }
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
