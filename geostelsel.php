<?php

require_once 'geostelsel.civix.php';

function geostelsel_civicrm_tabs(&$tabs, $contactID) {
  $config = CRM_Geostelsel_Config_Toegang::singleton();

  //unset the tab for iban accounts via custom fields and set our own tab for
  //display the iban accounts
  $tab_id = 'custom_'.$config->getToegangCustomGroup('id');
  $tabExists = false;
  $weight = 0;
  $count = 0;
  foreach($tabs as $key => $tab) {
    if ($tab['id'] == $tab_id) {
      unset($tabs[$key]);
      $weight = $tab['weight'];
      $count = $tab['count'];
      $tabExists = true;
    }
  }

  if ($tabExists) {
    $url = CRM_Utils_System::url('civicrm/contact/toegangsgegevens', "reset=1&cid=$contactID&snippet=1");
    //Count rules
    $tabs[] = array(
      'id' => 'toegangsgegevens',
      'url' => $url,
      'count' => $count,
      'title' => ts('Toegangsgegevens'),
      'weight' => $weight,
    );
  }

}

function geostelsel_civicrm_customFieldOptions($fieldID, &$options, $detailedFormat = false ) {

  // Test of performance hiervan beter is, twee config-klasses worden immers verder helemaal niet gebruikt
  $cfsp = CRM_Spgeneric_CustomField::singleton();

  //voeg groepen toe aan veld hoofdgroep op de afdelingskaart
  if ($fieldID == $cfsp->getFieldId('afdeling_groep', 'afdeling_groep')) {
    $group_ids = CRM_Core_PseudoConstant::group();
    $groups = CRM_Contact_BAO_Group::getGroupsHierarchy($group_ids, NULL, '&nbsp;&nbsp;', TRUE);
    foreach($groups as $gid => $title) {
      if ($detailedFormat) {
        $options['group_id_'.$gid]['id'] = 'group_id_'.$gid;
        $options['group_id_'.$gid]['value'] = $gid;
        $options['group_id_'.$gid]['label'] = $title;
      } else {
        $options[$gid] = $title;
      }
    }
  }
  //voeg groep opties toe aan het veld groep bij toegangsgegevems
  if ($fieldID == $cfsp->getFieldId('Toegangsgegevens', 'group_id')) {
    $group_ids = CRM_Core_PseudoConstant::group();
    $groups = CRM_Contact_BAO_Group::getGroupsHierarchy($group_ids, NULL, '&nbsp;&nbsp;', TRUE);
    foreach($groups as $gid => $title) {
      if ($detailedFormat) {
        $options[$gid]['id'] = 'group_id_'.$gid;
        $options[$gid]['value'] = $gid;
        $options[$gid]['label'] = $title;
      } else {
        $options[$gid] = $title;
      }
    }
  }
}

/** 
 * Update all contacts after a relationship between 
 * afdeling and regio or regio and province is changed
 * 
 * Implementation of hook_civicrm_civicrm_post
 * 
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_civicrm_post
 */
function geostelsel_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($objectName == 'Relationship') {
    $config = CRM_Geostelsel_Config::singleton();
    //if relationship is between afdeling and regio or between regio and provincie
    //also make sure all contacts with automatic geostelsel will be updated
    $rel_type_id = array(
      $config->getRegioRelationshipTypeId(),
      $config->getProvincieRelationshipTypeId(),
    );
    if (in_array($objectRef->relationship_type_id, $rel_type_id)) {
      _geostelsel_force_to_run_update_cron();
    }
  }
  if ($objectName == 'Address') {
    $config = CRM_Geostelsel_Config::singleton();
    $repo = CRM_Geostelsel_GeoInfo_Repository::singleton();
    $postcode_table = $config->getPostcodeCustomGroup('table_name');
    $gemeente_field = $config->getPostcodeGemeenteCustomField('column_name');
    $sql = "SELECT `{$postcode_table}`.`{$gemeente_field}` AS `gemeente`, `postal_code`, `contact_id`, civicrm_address.id as address_id FROM `civicrm_address` INNER JOIN `{$postcode_table}` ON `civicrm_address`.`id` = `{$postcode_table}`.`entity_id` WHERE `civicrm_address`.`is_primary` = 1 AND `civicrm_address`.`id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($objectId, 'Integer')));
    if ($dao->fetch() && $dao->address_id) {
      $provincie = _geostelsel_get_province_from_address($dao->address_id);
      $repo->updateContact($dao->contact_id, $dao->gemeente, $provincie);
    }
  }
}

function geostelsel_civicrm_pre( $op, $objectName, $id, &$params ) {
  if ($objectName == 'Group') {
    //if user has not permission to manage groups then add the parents of the access groups
    if (!CRM_Core_Permission::check('administer reserved groups')) {
      if (isset($params['created_id'])) {
        $contactID = $params['created_id'];
      } else {
        $session = CRM_Core_Session::singleton();
        $contactID = $session->get('userID');
      }
      
      $group_access = new CRM_Geostelsel_Groep_ParentGroup();
      $params['parents'] = implode(",",$group_access->accessToGroups($contactID));
    }
  }
}

function _geostelsel_get_province_from_address($address_id) {
  $sql = "SELECT civicrm_address.postal_code, civicrm_state_province.name as provincie FROM civicrm_address LEFT JOIN civicrm_state_province ON civicrm_state_province.country_id = civicrm_address.country_id AND civicrm_state_province.id = civicrm_address.state_province_id WHERE civicrm_address.id = %1";
  $params[1] = array($address_id, 'Integer');
  $dao = CRM_Core_DAO::executeQuery($sql, $params);
  $provincie = "";
  if ($dao->fetch()) {
    if (!empty($dao->provincie)) {
      $provincie = $dao->provincie;
    } elseif (!empty($dao->postal_code)) {
      $postcode = str_replace(" ", "", $dao->postal_code);
      $provincie = CRM_Core_DAO::singleValueQuery("SELECT provincie from civicrm_postcodenl where postcode_nr = '".substr($postcode, 0, 4)."' and postcode_letter = '".substr($postcode, 4, 2)."' limit 0,1");
    }
  }
  return $provincie;
}

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
        $sql = "SELECT `contact_id`, `postal_code` FROM `civicrm_address` WHERE `is_primary` = '1' AND `id` = %1";
        $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($field['entity_id'], 'Integer')));
        if ($dao->fetch() && $dao->contact_id) {
          $provincie = _geostelsel_get_province_from_address($field['entity_id']);
          $repo->updateContact($dao->contact_id, $field['value'], $provincie);
        }
      }
    }
  } elseif ($groupID == $config->getGeostelselCustomGroup('id')) {
    foreach($params as $field) {
      if ($field['custom_field_id'] == $config->getHandmatigeInvoerField('id') && empty($field['value'])) {
        $postcode_table = $config->getPostcodeCustomGroup('table_name');
        $gemeente_field = $config->getPostcodeGemeenteCustomField('column_name');
        $sql = "SELECT `{$postcode_table}`.`{$gemeente_field}` AS `gemeente`, `postal_code`, civicrm_address.id as address_id FROM `civicrm_address` INNER JOIN `{$postcode_table}` ON `civicrm_address`.`id` = `{$postcode_table}`.`entity_id` WHERE `civicrm_address`.`is_primary` = 1 AND `civicrm_address`.`contact_id` = %1";
        $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($field['entity_id'], 'Integer')));
        if ($dao->fetch() && $dao->address_id) {
          $provincie = _geostelsel_get_province_from_address($dao->address_id);
          $repo->updateContact($field['entity_id'], $dao->gemeente, $provincie);
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