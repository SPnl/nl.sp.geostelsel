<?php

/**
 * This is BAO class without a DAO because there is no underlying data object
 * It is a wrapper around a custom group
 *
 * Class CRM_Geostelsel_BAO_Toegangsgegevens
 */
class CRM_Geostelsel_BAO_Toegangsgegevens {

  public static function buildTree($contact_id) {
    $type_options = self::getTypeOptions();
    $contact_options = self::getContactOptions();
    $group_options = self::getGroupOptions();
    $link_options = self::getLinkOptions();

    $sql = "SELECT *, 1 as is_parent FROM civicrm_value_toegangsgegevens WHERE (parent_id IS NULL or parent_id = '') AND entity_id = %1
            UNION
            SELECT *, 0 AS is_parent FROM civicrm_value_toegangsgegevens WHERE parent_id IS NOT NULL and parent_id != '' AND entity_id = %1
            ORDER BY is_parent DESC, weight ASC";
    $params[1] = array($contact_id, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $values = array();
    while ($dao->fetch()) {
      $value = array();
      $value['type'] = $dao->type;
      $value['link'] = $dao->link;
      $value['parent_id'] = $dao->parent_id;
      $value['id'] = $dao->id;
      $value['toegang_tot_contacten_van'] = $dao->toegang_tot_contacten_van;
      $value['group_id'] = $dao->group_id;
      $value['toegang_tot_contacten_van_label'] = $contact_options[$dao->toegang_tot_contacten_van];
      $value['group_id_label'] = $group_options[$dao->group_id];
      $value['weight'] = $dao->weight;
      $value['type_label'] = $type_options[$dao->type];
      $value['link_label'] = $link_options[$dao->link];
      $value['children'] = array();
      if ($dao->is_parent) {
        $values[] = $value;
      } else {
        foreach($values as $i => $parents) {
          if ($parents['id'] == $value['parent_id']) {
            $values[$i]['children'][] = $value;
          }
        }
      }
    }

    return $values;
  }

  public static function getValues($id) {
    $type_options = self::getTypeOptions();
    $contact_options = self::getContactOptions();
    $group_options = self::getGroupOptions();
    $link_options = self::getLinkOptions();

    $sql = "SELECT *, 1 as is_parent FROM civicrm_value_toegangsgegevens WHERE id = %1";
    $params[1] = array($id, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      $value = array();
      $value['type'] = $dao->type;
      $value['link'] = $dao->link;
      $value['parent_id'] = $dao->parent_id;
      $value['id'] = $dao->id;
      $value['toegang_tot_contacten_van'] = $dao->toegang_tot_contacten_van;
      $value['group_id'] = $dao->group_id;
      $value['toegang_tot_contacten_van_label'] = $contact_options[$dao->toegang_tot_contacten_van];
      $value['group_id_label'] = $group_options[$dao->group_id];
      $value['weight'] = $dao->weight;
      $value['type_label'] = $type_options[$dao->type];
      $value['link_label'] = $link_options[$dao->link];
      return $value;
    }

    return false;
  }

  public static function generateAclWhere(&$tables, &$whereTables, &$contactID, &$where) {
    $tree = self::buildTree($contactID);
    $whereClauses = self::buildWhereFromTree($tree, $tables, $whereTables);
    if (strlen($whereClauses)) {
      if (strlen($where) && stripos($where, " ( ( ") === 0) {
      	$where = substr($where, 0, -2);
      	$where .= " OR ( ".$whereClauses." ) ) ";
      } elseif (strlen($where)) {
        $where .= " OR (".$whereClauses.")";
      } else {
      	$where .= " (".$whereClauses.")";
      }
    }
  }

  public static function buildWhereFromTree($tree, &$tables, &$whereTables) {
    $whereClauses = '';
    foreach($tree as $leaf) {
      $whereClause = self::buildWhere($leaf, $tables, $whereTables);
      if (strlen($whereClause)) {
        if (strlen($whereClauses)) {
          $whereClauses .= ' '.$leaf['link'].' ';
        }
        $whereClauses .= ' ('.$whereClause.')';
      }
    }
    return $whereClauses;
  }

  public static function buildWhere($leaf, &$tables, &$whereTables) {
    $where = '';
    switch($leaf['type']) {
      case 'AfdelingMember':
        $where = self::getWhereForAfddeling($leaf['toegang_tot_contacten_van'], $tables, $whereTables);
        break;
      case 'GroupMember':
        $where = self::getWhereForGroup($leaf['group_id'], $tables, $whereTables);
        break;
      default:
        if (count($leaf['children'])) {
          $childrenWhere = self::buildWhereFromTree($leaf, $tables, $whereTables);
          if (strlen($childrenWhere)) {
            $where .= "(".$childrenWhere.")";
          }
        }
        break;
    }
    return $where;
  }

  public static function getWhereForGroup($group_id, &$tables, &$whereTables) {
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_group where id = ".$group_id);
    $clauses = array();
    while ($dao->fetch()) {
      if (empty($dao->where_clause)) {
        $tables['civicrm_group_'.$group_id] = $whereTables['civicrm_group_'.$group_id] = "LEFT JOIN `civicrm_group_contact` `civicrm_group_".$group_id."` ON `civicrm_group_".$group_id."`.contact_id = contact_a.id ";
        $clauses[] = "`civicrm_group_".$group_id."`.group_id = '".$group_id."' AND status = 'Added'";
      } else {
        $selects = unserialize($dao->select_tables);
        foreach ($selects as $table => $join) {
          $tables[$table] = $join;
        }
        $wheres = unserialize($dao->where_tables);
        foreach ($wheres as $table => $join) {
          $whereTables[$table] = $join;
        }
        $clauses[] = $dao->where_clause;
      }
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

    $contact_sub_type = CRM_Core_DAO::singleValueQuery("SELECT contact_sub_type from civicrm_contact where id = %1", array(1=>array($afdeling_id, 'Integer')));
    if (stristr($contact_sub_type, CRM_Core_DAO::VALUE_SEPARATOR."SP_Landelijk".CRM_Core_DAO::VALUE_SEPARATOR)) {
      return "
      (
        membership_access.membership_type_id IN ({$mtype_ids})
        AND (
          membership_access.status_id IN ({$mstatus_ids})
          OR
          (membership_access.status_id = '{$membership_type->getDeceasedStatusId()}' AND (membership_access.end_date >= NOW() - INTERVAL 3 MONTH))
        )
      ) OR contact_a.id = {$afdeling_id}";
    } else {
      return "
      (
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
        )
      ) OR contact_a.id = {$afdeling_id}";
    }
  }

  public static function getTypeOptions() {
    $return = CRM_Core_OptionGroup::values('access_type');
    unset($return['Grouping']);
    return $return;
  }

  public static function getContactOptions() {
    $return = array();
    $return[] = '';
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_contact` WHERE `contact_sub_type` LIKE '%SP_Afdeling%' OR `contact_sub_type` LIKE '%SP_Regio%' OR `contact_sub_type` LIKE '%SP_Provincie%' OR `contact_sub_type` LIKE '%SP_Landelijk%' ORDER BY `display_name`");
    while($dao->fetch()) {
      $return[$dao->id] = $dao->display_name;
    }
    return $return;
  }

  public static function getGroupOptions() {
    $groupHierarchy = CRM_Contact_BAO_Group::getGroupsHierarchy(CRM_Core_PseudoConstant::nestedGroup(FALSE), NULL, '&nbsp;&nbsp;', TRUE);
    $return[] = '';
    $return = $return + $groupHierarchy;
    return $return;
  }

  public static function getLinkOptions() {
    $return[] = '';
    $return = $return + CRM_Core_OptionGroup::values('access_link');
    return $return;
  }

}
