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

  public static function getWhereForGroup($group_id, &$tables, &$whereTables) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_group where id = ".$group_id);
    $clauses = array();
    while ($dao->fetch()) {
      $selects = unserialize($dao->select_tables);
      foreach($selects as $table => $join) {
        $tables[$table] = $join;
      }
      $wheres = unserialize($dao->where_tables);
      foreach($wheres as $table => $join) {
        $whereTables[$table] = $join;
      }
      $clauses[] = $dao->where_clause;
    }

    if (!empty($clauses)) {
      return " ( ".implode(" AND ", $clauses)." ) ";
    }
    return "";
  }

  public static function getWhereForAfddeling($afdeling_id, &$tables, &$whereTables) {
    $config = CRM_Geostelsel_Config::singleton();
    $table = $config->getGeostelselCustomGroup('table_name');
    $afdeling = $config->getAfdelingsField('column_name');
    $regio = $config->getRegioField('column_name');
    $provincie = $config->getProvincieField('column_name');
    $tables['geostelsel'] = $whereTables['geostelsel'] = "LEFT JOIN {$table} geostelsel ON contact_a.id = geostelsel.entity_id";

    //add active membership
    $membership_type = CRM_Geostelsel_Config_MembershipTypes::singleton();
    $membership_table = 'civicrm_membership';
    $tables['membership_access'] = $whereTables['membership_access'] = "LEFT JOIN {$membership_table} membership_access ON contact_a.id = membership_access.contact_id";
    $mtype_ids = implode(", ", $membership_type->getMembershipTypeIds());
    $mstatus_ids = implode(", ", $membership_type->getStatusIds());


    return "
        (
          geostelsel.`{$afdeling}` = {$afdeling_id}
          OR geostelsel.`{$regio}` = {$afdeling_id}
          OR geostelsel.`{$provincie}` = {$afdeling_id}
        )
        AND
        (
          membership_access.membership_type_id IN ({$mtype_ids})
          AND (
            membership_access.status_id IN ({$mstatus_ids})
            OR
            (membership_access.status_id = '{$membership_type->getDeceasedStatusId()}' AND (membership_access.end_date >= NOW() - INTERVAL 3 MONTH))
          )
        )";
  }

}