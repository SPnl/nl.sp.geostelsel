<?php

class CRM_Geostelsel_GeoInfo_Utils {
  
  public static function getRegioByAfdeling($afdelings_contact_id) {
    try {
      $config = CRM_Geostelsel_Config::singleton();
      $params['return'] = 'contact_id_b';
      $params['relationship_type_id'] = $config->getRegioRelationshipTypeId();
      $params['contact_id_a'] = $afdelings_contact_id;
      $params['status_id'] = CRM_Contact_BAO_Relationship::CURRENT;
      
      $relationships = CRM_Contact_BAO_Relationship::getRelationship($afdelings_contact_id, CRM_Contact_BAO_Relationship::CURRENT, 0, 0, 0, NULL, NULL, false, $params);
      if (count($relationships)) {
        $relationship = reset($relationships);
        $contact_id = $relationship['contact_id_b'];
      }
      return $contact_id;
    } catch (Exception $ex) {
      throw $ex;
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
      $params['status_id'] = CRM_Contact_BAO_Relationship::CURRENT;

      $relationships = CRM_Contact_BAO_Relationship::getRelationship($regio_contact_id, CRM_Contact_BAO_Relationship::CURRENT, 0, 0, 0, NULL, NULL, false, $params);
      if (count($relationships)) {
        $relationship = reset($relationships);
        $contact_id = $relationship['contact_id_b'];
      }

      return $contact_id;
    } catch (Exception $ex) {
      //do nothing
    }
    return false;
  }
  
}
