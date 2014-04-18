<?php

/* 
 * interface for target local/regio relationship
 * 
 * This class is repsonible for linking regio to relationships
 * 
* @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
* @date 28 Mar 2014
 */

class CRM_Geostelsel_LocalTarget extends CRM_Autorelationship_TargetInterface {
  
  public function getEntitySystemName() {
    return 'local_regio';
  }
  
  public function getEntityHumanName() {
    return ts('Lid op basis van afdeling/regio relatie');
  }
  
  public function getMatcher() {
    return new CRM_Geostelsel_LocalMatcher($this);
  }
  
  public function listEntitiesForTarget($targetContactId) {
    $sql = "SELECT * FROM `civicrm_autorelationship_local_member` WHERE `contact_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array('1' => array($targetContactId, 'Integer')));

    $rules = array();
    $weight = 1;
    while ($dao->fetch()) {
      $rule['entity_id'] = $dao->id;
      $rule['label'] = '';
      $rule['weight'] = $weight;
      $rules[] = $rule;
      
      $weight++;
    }
    
    return $rules;
  }
  
  protected function deleteTargetEntity($entityId, $targetContactId) {
    $sql = "DELETE FROM `civicrm_autorelationship_local_member` WHERE `id` = %2 AND `contact_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
        '1' => array($targetContactId, 'Integer'),
        '2' => array($entityId, 'Integer')
      ));
  }
  
  public function getAddFormUrl() {
    return 'civicrm/autorelationship/addrule/local_regio';
  }
  
}

