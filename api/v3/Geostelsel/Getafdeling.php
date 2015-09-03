<?php

/**
 * API function to retrive a list of afdelingen based on postcode or (partial)
 * name of the afdeling
 *
 * @param $api_params
 */
function civicrm_api3_geostelsel_getafdeling($api_params) {
  if (empty($api_params['name'])) {
    return civicrm_api3_create_error('The parameter name is empty');
  }

  $postcode_nr = substr($api_params['name'], 0 , 4);
  $postcode_letter = substr($api_params['name'],4,3);
  if ($postcode_letter) {
    $postcode_letter = trim($postcode_letter);
    if (strlen($postcode_letter) != 2) {
      $postcode_letter = false;
    }
  }
  if (!is_numeric($postcode_nr)) {
    $postcode_nr = false;
  }

  if ($postcode_letter && $postcode_nr) {
    $sql = "SELECT provincie, gemeente FROM civicrm_postcodenl where (postcode_nr = %1 and postcode_letter like %2) or gemeente like %3 GROUP BY provincie, gemeente";
    $postcodeParams[1] = array($postcode_nr, 'Integer');
    $postcodeParams[2] = array('%'.$postcode_letter.'%', 'String');
    $postcodeParams[3] = array('%'.$api_params['name'].'%', 'String');
  } elseif ($postcode_nr) {
    $sql = "SELECT provincie, gemeente FROM civicrm_postcodenl where postcode_nr = %1 or gemeente like %2 GROUP BY provincie, gemeente";
    $postcodeParams[1] = array($postcode_nr, 'Integer');
    $postcodeParams[2] = array('%'.$api_params['name'].'%', 'String');
  } else {
    $sql = "SELECT provincie, gemeente FROM civicrm_postcodenl where gemeente like %1 GROUP BY provincie, gemeente";
    $postcodeParams[1] = array('%'.$api_params['name'].'%', 'String');
  }

  $dao = CRM_Core_DAO::executeQuery($sql, $postcodeParams);
  $where = "";
  while($dao->fetch()) {
    $where .= " OR g.gemeente = '".$dao->gemeente." (".$dao->provincie.")'";
  }

  $return = array();
  if (strlen($where)) {
    $config = CRM_Geostelsel_Config::singleton();
    $contact_sql = "SELECT c.id, c.display_name
                  FROM civicrm_contact c
                  LEFT JOIN `{$config->getGemeenteCustomGroup('table_name')}` `g` ON c.id = g.entity_id
                  WHERE c.display_name LIKE %1
                  ".$where." ORDER BY c.display_name";
    $contactParams[1] = array('%'.$api_params['name'].'%', 'String');
    $contactDao = CRM_Core_DAO::executeQuery($contact_sql, $contactParams);
    while ($contactDao->fetch()) {
      $return[$contactDao->id] = array(
        'id' => $contactDao->id,
        'display_name' => $contactDao->display_name
      );
    }
  }

  return civicrm_api3_create_success($return);
}