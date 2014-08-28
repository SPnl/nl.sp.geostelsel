<?php

require_once 'geostelsel.civix.php';

/** 
 * Update all contacts who have this gemeente in their primary address
 * 
 * Implementation of hook_civicrm_custom
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_custom
 */
function geostelsel_civicrm_custom($op,$groupID, $entityID, &$params ) {
  $repo = CRM_Geostelsel_GeoInfo_Repository::singleton();
  $config = CRM_Geostelsel_Config::singleton();
  if ($groupID == $config->getGemeenteCustomGroup('id')) {
    //afdeling has changed the list of gemeentes, make sure all contacts are updated
    _geostelsel_force_to_run_update_cron();
  } elseif ($groupID == $config->getPostcodeCustomGroup('id')) {
    //gemeente field of an address has been changed
    foreach($params as $field) {
      if ($field['custom_field_id'] == $config->getPostcodeGemeenteCustomField('id')) {
        $sql = "SELECT `contact_id` FROM `civicrm_address` WHERE `is_primary` = '1' AND `id` = %1";
        $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($field['entity_id'], 'Integer')));
        if ($dao->fetch()) {
          $repo->updateContact($dao->contact_id, $field['value']);
        }
      }
    }
  } elseif ($groupID == $config->getGeostelselCustomGroup('id')) {
    foreach($params as $field) {
      if ($field['custom_field_id'] == $config->getHandmatigeInvoerField('id') && empty($field['value'])) {
        $postcode_table = $config->getPostcodeCustomGroup('table_name');
        $gemeente_field = $config->getPostcodeGemeenteCustomField('column_name');
        $sql = "SELECT `{$postcode_table}`.`{$gemeente_field}` AS `gemeente` FROM `civicrm_address` INNER JOIN `{$postcode_table}` ON `civicrm_address`.`id` = `{$postcode_table}`.`entity_id` WHERE `civicrm_address`.`is_primary` = 1 AND `civicrm_address`.`contact_id` = %1";
        $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($field['entity_id'], 'Integer')));
        if ($dao->fetch()) {
          $repo->updateContact($field['entity_id'], $dao->gemeente);
        }
      }
    }
  }  
}

/**
 * Implementation of hook_civicrm_validateForm
 * 
 * Validate that a gemeente is only added once to an afdeling
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_validateForm
 */
function geostelsel_civicrm_validateForm( $formName, &$fields, &$files, &$form, &$errors ) {
  if ($formName == 'CRM_Contact_Form_CustomData') {
    $repo = CRM_Geostelsel_GeoInfo_Repository::singleton();
    $config = CRM_Geostelsel_Config::singleton();
    $custom_id = 'custom_'.$config->getGemeenteCustomField('id');
    foreach($fields as $key => $value) {
      if (stripos($key, $custom_id) === 0 && strripos($key, '_id') !== false) {
        $data = $repo->getGeoInfoByGemeente($value);
        if ($data === false) {
          continue;
        }
        if ($data->getAfdelingsContactId() > 0 && $data->getAfdelingsContactId() != $form->_entityId) {
          $afdelings_naam = "";
          try {
            $afdelings_naam = civicrm_api3('Contact', 'getvalue', array('return' => 'display_name', 'id' => $data->getAfdelingsContactId()));
          } catch (Exception $e) {
            
          }
          $fieldKey = str_replace("_id", "", $key);
          $errors[$fieldKey] = ts('Gemeente "'.$fields[$fieldKey].'" bestaat al bij "'.$afdelings_naam.'"');
        }
      }
    }
  }  
}


function _geostelsel_force_to_run_update_cron() {
  CRM_Core_BAO_Setting::setItem('1', 'nl.sp.geostelsel', 'api.geostelsel.update.to_run');
}

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
  try {
    $extensions = civicrm_api3('Extension', 'get');  
    foreach($extensions['values'] as $ext) {
      if ($ext['status'] == 'installed') {
        switch ($ext['key']) {
          case 'org.civicoop.postcodenl':
            $isPostcodeNLInstalled = true;
            break;
        }
      }
    }    
  } catch (Exception $e) {
    $error = true;
  }
  
  
  if ($error || !$isPostcodeNLInstalled) {
    throw new Exception('This extension requires org.civicoop.postcodenl');
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