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
  $interfaces[] = new CRM_Geostelsel_LocalTarget();
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
  if ($objectName == 'Relationship' && $objectRef instanceof CRM_Contact_DAO_Relationship) {
    $matcherTypes = CRM_Geostelsel_LocalMatcher::getMatchRelationshipTypeIds();
    $matcherTypeRegioRelationship = CRM_Geostelsel_LocalMatcher::getLocalRegioRelationshipTypeId();
    if (in_array($objectRef->relationship_type_id, $matcherTypes)) {
      //de relatie is een lid van op basis van gemeente e.d.
      //gebruik contact_id_a van deze relatie als een source contact
      $matcher = $factory->getMatcherForEntity('local_regio', array('relationship' => $objectRef));
      $matcher->matchAndCreateForSourceContact();
    } elseif ($objectRef->relationship_type_id == $matcherTypeRegioRelationship) {
      //de relatie is een relatie van type lokaal/regio
      //gebruik contact_id_b van de relatie als de target contact id
      $interface = $factory->getInterfaceForEntity('local_regio');
      $matcher = $interface->getMatcher();
      
      //find the entityID of a rule on the target (if not found do not do any matching)
      //if configured correctly there is only one rule
      $rules = $interface->listEntitiesForTarget($objectRef->contact_id_b);
      foreach($rules as $rule) {  
        $matcher->matchAndCreateForTargetContactAndEntityId($objectRef->contact_id_b, $rule['entity_id']);
      }
    }
  }
}