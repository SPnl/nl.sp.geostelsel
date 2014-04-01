<?php

/* 
 * interface for target gemeente
 * 
 * This class is repsonible for linking gemeente to relationships
 * 
* @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
* @date 28 Mar 2014
 */

class CRM_Geostelsel_GemeenteTarget extends CRM_Autorelationship_TargetInterface {
  
  public function getEntitySystemName() {
    return 'gemeente';
  }
  
  public function getEntityHumanName() {
    return ts('Gemeente');
  }
  
  public function getMatcher() {
    return new CRM_Geostelsel_GemeenteMatcher($this);
  }
  
  public function listEntitiesForTarget($targetContactId) {
    $sql = "SELECT * FROM `civicrm_autorelationship_contact_gemeente` WHERE `contact_id` = %1 ORDER BY `gemeente`";
    $dao = CRM_Core_DAO::executeQuery($sql, array('1' => array($targetContactId, 'Integer')));

    $cities = array();
    $weight = 1;
    while ($dao->fetch()) {
      $city['entity_id'] = $dao->id;
      $city['label'] = $dao->gemeente;
      $city['weight'] = $weight;
      $cities[] = $city;
      
      $weight++;
    }
    
    return $cities;
  }
  
  protected function deleteTargetEntity($entityId, $targetContactId) {
    $sql = "DELETE FROM `civicrm_autorelationship_contact_gemeente` WHERE `id` = %2 AND `contact_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
        '1' => array($targetContactId, 'Integer'),
        '2' => array($entityId, 'Integer')
      ));
  }
  
  public function getAddFormUrl() {
    return 'civicrm/autorelationship/addrule/gemeente';
  }
  
}

