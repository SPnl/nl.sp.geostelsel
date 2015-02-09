<?php

/**
 * GemeentesLijst.Update API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_gemeentes_lijst_update_spec(&$spec) {
}

/**
 * GemeentesLijst.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_gemeentes_lijst_update($params) {
  $returnValues = array();
  
  $option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'gemeente'));
  
  //first add new gemeentes to the list
  $count = 0;
  $gemeentes = CRM_Core_DAO::executeQuery("SELECT DISTINCT `civicrm_postcodenl`.`gemeente`, `civicrm_postcodenl`.`provincie` FROM `civicrm_postcodenl`");
  while ($gemeentes->fetch()) {
    $key = $gemeentes->gemeente.' ('.$gemeentes->provincie.')';
    $sql = "SELECT COUNT(*) FROM `civicrm_option_value` where `option_group_id` = %1 AND `value` = %2";
    $params = array();
    $params[1] = array($option_group_id, 'Integer');
    $params[2] = array($key, 'String');
    $exist = CRM_Core_DAO::singleValueQuery($sql, $params);
    if (!$exist) {
      $insert = "INSERT INTO `civicrm_option_value` (`option_group_id`, `value`, `label`, `grouping`, `is_reserved`, `is_active`) VALUES (%1, %2, %3, %4, 1, 1);";
      $grouping = $gemeentes->provincie;

      CRM_Core_DAO::executeQuery($insert, array(
        1 => array($option_group_id, 'Integer'),
        2 => array($key, 'String'),
        3 => array($key, 'String'),
        4 => array($grouping, 'String'),
      ));

      $count ++;
    }
  }

  $returnValues['message'] = 'Inserted '.$count.' gemeentes';
  
  return civicrm_api3_create_success($returnValues, $params, 'GemeentesLijst', 'update');
}

