<?php

/**
 * Afdeling add Gemeente naam
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_afdeling_gemeenteUpdate($params) {
  $returnValues = array();
  
  $count = 0;
  $config = CRM_Geostelsel_Config::singleton();
  $option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'gemeente'));
  $table = $config->getGemeenteCustomGroup('table_name');
  $field = $config->getGemeenteCustomField('column_name');
  $contacts = civicrm_api3('Contact', 'get', array('contact_sub_type' => 'SP_Afdeling'));
  
  foreach($contacts['values'] as $contact) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `{$table}` WHERE `entity_id` = %1", array(1=>array($contact['id'], 'Integer')));
    if ($dao->fetch()) {
      //afdeling heeft al gemeentes gekoppeld
      continue;
    }
    
    $gemeente = str_ireplace("SP-afdeling ", "", $contact['display_name']);
    
    try {
      $value = civicrm_api3('OptionValue', 'getvalue', array(
        'return' => 'value',
        'option_group_id' => $option_group_id,
        'value' => $gemeente,
      ));
      
      $sql = "INSERT INTO `{$table}` (`entity_id`, `{$field}`) VALUES (%1, %2)";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($contact['id'], 'Integer'),
        2 => array($value, 'String')
      ));
      
      $count ++;
      
    } catch (Exception $e) {
      //do nothing
    }
  }
  
  if ($count) {
    _geostelsel_force_to_run_update_cron();
  }

  $returnValues['message'] = 'Updated '.$count.' afdelingen';
  
  return civicrm_api3_create_success($returnValues, $params, 'GemeentesLijst', 'update');
}

