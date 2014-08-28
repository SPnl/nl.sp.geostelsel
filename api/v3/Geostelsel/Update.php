<?php

/**
 * Geostelsel.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_geostelsel_update($api_params) {
  $returnValues = array();
  
  $limit = 1000;
  
  $to_run = CRM_Core_BAO_Setting::getItem('nl.sp.geostelsel', 'api.geostelsel.update.to_run');
  if ($to_run === '0' && empty($api_params['force'])) {
    $returnValues['message'] = 'No need for update';  
    return civicrm_api3_create_success($returnValues, $params, 'Geostelsel', 'update');
  }
  
  $offset = CRM_Core_BAO_Setting::getItem('nl.sp.geostelsel', 'api.geostelsel.update.offset');
  if (empty($offset)) {
    $offset = 0;
  }
  
  $repo = CRM_Geostelsel_GeoInfo_Repository::singleton();
  $config = CRM_Geostelsel_Config::singleton();
  $postcode_table = $config->getPostcodeCustomGroup('table_name');
  $gemeente_field = $config->getPostcodeGemeenteCustomField('column_name');
  
  $count = 0;
  $dao =CRM_Core_DAO::executeQuery("SELECT `{$postcode_table}`.`{$gemeente_field}` AS `gemeente`, `civicrm_address`.`contact_id` AS `contact_id` FROM `{$postcode_table}` INNER JOIN `civicrm_address` ON `{$postcode_table}`.`entity_id` = `civicrm_address`.`id` LEFT JOIN `civicrm_contact` ON `civicrm_address`.`contact_id` = `civicrm_contact`.`id` WHERE `civicrm_address`.`is_primary` = '1' AND `civicrm_contact`.`is_deleted` = '0' LIMIT {$offset}, {$limit}");
  while($dao->fetch()) {
    $count ++;
    $repo->updateContact($dao->contact_id, $dao->gemeente);
  }
  
  if ($count < $limit) {
    $newOffset = 0;
    $to_run = '0';
  } else {
    $newOffset = $offset + $limit;
    $to_run = '1';
  }
  
  
  CRM_Core_BAO_Setting::setItem($to_run, 'nl.sp.geostelsel', 'api.geostelsel.update.to_run');
  CRM_Core_BAO_Setting::setItem($newOffset, 'nl.sp.geostelsel', 'api.geostelsel.update.offset');
  
  $returnValues['message'] = 'Updated '.$count.' contacts';
  
  return civicrm_api3_create_success($returnValues, $params, 'Geostelsel', 'update');
}
