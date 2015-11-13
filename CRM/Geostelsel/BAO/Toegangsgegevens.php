<?php

/**
 * This is BAO class without a DAO because there is no underlying data object
 * It is a wrapper around a custom group
 *
 * Class CRM_Geostelsel_BAO_Toegangsgegevens
 */
class CRM_Geostelsel_BAO_Toegangsgegevens {

  public static function buildTree($contact_id) {
    $sql = "SELECT *, 1 as is_parent FROM civicrm_value_toegangsgegevens WHERE parent_id IS NULL or parent_id = '' AND entity_id = %1
            UNION
            SELECT * 0 AS is_parent FROM civicrm_value_toegangsgegevens WHERE parent_id IS NOT NULL and parent_id != '' AND entity_id = %1
            ORDER BY is_parent DESC, weight ASC";
    $params[1] = array($contact_id, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql);
    $values = array();
    while ($dao->fetch()) {
      $value = array();
      $value['type'] = $dao->type;
      $value['link'] = $dao->link;
      $value['parent_id'] = $dao->parent_id;
      $value['id'] = $dao->id;
      $value['toegang_tot_contacten_van'] = $dao->toegang_tot_contacten_van;
      $value['group_id'] = $dao->group_id;
      $value['weight'] = $dao->weight;
      if ($dao->is_parent) {
        $values[] = $value;
      } else {
        foreach($values as $i => $parents) {
          if ($parents['id'] == $value['parent_id']) {
            $values[$i]['children'] = $value;
          }
        }
      }
    }

    return $values;
  }

}