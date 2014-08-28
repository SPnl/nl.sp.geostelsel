<?php

class CRM_Geostelsel_GeoInfo_Utils {
  
  public static function getRegioByAfdeling($afdelings_contact_id) {
    try {
      $config = CRM_Geostelsel_Config::singleton();
      $params['return'] = 'contact_id_b';
      $params['relationship_type_id'] = $config->getRegioRelationshipTypeId();
      $params['contact_id_a'] = $afdelings_contact_id;
      $params['is_active'] = 1;
      $contact_id = civicrm_api3('Relationship', 'getvalue', $params);
      return $contact_id;
    } catch (Exception $ex) {
      //do nothing
    }
    return false;
  }
  
  public static function getProvincieByRegio($regio_contact_id) {
    try {
      $config = CRM_Geostelsel_Config::singleton();
      $params['return'] = 'contact_id_b';
      $params['relationship_type_id'] = $config->getProvincieRelationshipTypeId();
      $params['contact_id_a'] = $regio_contact_id;
      $params['is_active'] = 1;
      $contact_id = civicrm_api3('Relationship', 'getvalue', $params);
      return $contact_id;
    } catch (Exception $ex) {
      //do nothing
    }
    return false;
  }
  
}
