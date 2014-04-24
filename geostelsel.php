<?php

require_once 'geostelsel.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function geostelsel_civicrm_config(&$config) {
  _geostelsel_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function geostelsel_civicrm_xmlMenu(&$files) {
  _geostelsel_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function geostelsel_civicrm_install() {
  $error = false;
  $isPostcodeNLInstalled = false;
  $isAutorelationshipInstalled = false;
  try {
    $extensions = civicrm_api3('Extension', 'get');  
    foreach($extensions['values'] as $ext) {
      if ($ext['status'] == 'installed') {
        switch ($ext['key']) {
          case 'org.civicoop.postcodenl':
            $isPostcodeNLInstalled = true;
            break;
          case 'org.civicoop.autorelationship':
            $isAutorelationshipInstalled = true;
            break;
        }
      }
    }    
  } catch (Exception $e) {
    $error = true;
  }
  
  
  if ($error || !$isAutorelationshipInstalled || !$isPostcodeNLInstalled) {
    throw new Exception('This extension requires org.civicoop.postcodenl and org.civicoop.autorelationship');
  }
  
  return _geostelsel_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function geostelsel_civicrm_uninstall() {
  return _geostelsel_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function geostelsel_civicrm_enable() {
  return _geostelsel_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function geostelsel_civicrm_disable() {
  return _geostelsel_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function geostelsel_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _geostelsel_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function geostelsel_civicrm_managed(&$entities) {
  return _geostelsel_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function geostelsel_civicrm_caseTypes(&$caseTypes) {
  _geostelsel_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function geostelsel_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _geostelsel_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook__civicrm_autorelationship_targetinterfaces
 * 
 * @param array $interfaces
 */
function geostelsel_autorelationship_targetinterfaces(&$interfaces) {
  $interfaces[] = new CRM_Geostelsel_GemeenteTarget();
}

/**
 * Implementation of hook_civicrm_post
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function geostelsel_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  $factory = CRM_Autorelationship_TargetFactory::singleton();
  if ($objectName == 'Address' && $objectRef instanceof CRM_Core_DAO_Address) {  
    $matcher = $factory->getMatcherForEntity('gemeente', array('address' => $objectRef));
    $matcher->matchAndCreateForSourceContact();
  }
}

/**
 * Check if contact is a member of a local party or the local party it self
 * 
 * Implementation of hook_civicrm_aclWhereClause
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_aclWhereClause
 */
function geostelsel_civicrm_aclWhereClause( $type, &$tables, &$whereTables, &$contactID, &$where ) {
  $relationtypes = CRM_Geostelsel_RelationshipTypes::singleton();
  $kaderleden = $relationtypes->getKaderfunctieRelationshipTypeIds();
  $regios = $relationtypes->getRegioRelationshipTypeIds();
  $lokale_leden = $relationtypes->getLokaalLidRelationshipTypeIds();
  
  $tables['civicrm_relationship_r1'] = $whereTables['civicrm_relationship_r1'] = 
      " LEFT JOIN `civicrm_relationship` `geo_r1` ON  `contact_a`.`id` = `geo_r1`.`contact_id_a` AND `geo_r1`.`relationship_type_id` IN (".implode(",", $regios).")";
  $tables['civicrm_relationship_r2'] = $whereTables['civicrm_relationship_r2'] = 
      " LEFT JOIN `civicrm_relationship` `geo_r2` ON  `geo_r2`.`contact_id_b` = `geo_r1`.`contact_id_b` AND `geo_r2`.`relationship_type_id` IN (".implode(",", $kaderleden).")";
  $tables['civicrm_relationship_r3'] = $whereTables['civicrm_relationship_r3'] = 
      " LEFT JOIN `civicrm_relationship` `geo_r3` ON  `contact_a`.`id` = `geo_r3`.`contact_id_a` AND `geo_r3`.`relationship_type_id` IN (".implode(",", $lokale_leden).")";
  $tables['civicrm_relationship_r4'] = $whereTables['civicrm_relationship_r4'] = 
      " LEFT JOIN `civicrm_relationship` `geo_r4` ON  `geo_r4`.`contact_id_a` = `geo_r3`.`contact_id_b` AND `geo_r4`.`relationship_type_id` IN (".implode(",", $regios).")";
  $tables['civicrm_relationship_r5'] = $whereTables['civicrm_relationship_r5'] = 
      " LEFT JOIN `civicrm_relationship` `geo_r5` ON  `geo_r5`.`contact_id_b` = `geo_r4`.`contact_id_b` AND `geo_r5`.`relationship_type_id` IN (".implode(",", $kaderleden).")";
  
  $where .= " (`geo_r2`.`contact_id_a` = '".$contactID."' OR `geo_r5`.`contact_id_a` = '".$contactID."')";
  return true;
}