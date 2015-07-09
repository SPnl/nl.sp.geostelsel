<?php

class CRM_Geostelsel_Acl {

  public static function aclWhereClause( $type, &$tables, &$whereTables, &$contactID, &$where ) {
    if ( ! $contactID ) {
      return;
    }

    $config = CRM_Geostelsel_Config_Toegang::singleton();
    $toegang_tot_groep = array();
    $toegang_tot_afdelingen = array();
    $permission_table = $config->getToegangCustomGroup('table_name');
    $toegang_afdeling_field = $config->getToegangAfdelingCustomField('column_name');
    $toegang_groep_field = $config->getToegangGroepCustomField('column_name');

    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `{$permission_table}` WHERE `entity_id` = %1", array(1 => array($contactID, 'Integer')));
    while ($dao->fetch()) {
      $toegang_afdeling = $dao->$toegang_afdeling_field;
      $toegang_groep = $dao->$toegang_groep_field;
      if (!empty($toegang_afdeling)) {
        $toegang_tot_afdelingen[] = $toegang_afdeling;
      }
      if (!empty($toegang_groep)) {
        $toegang_tot_groep[] = $toegang_groep;
      }
    }

    $whereClauses = array();
    $whereClauses[] = self::toegangTotContactenVanAfdeling($type, $tables, $whereTables, $contactID, $where, $toegang_tot_afdelingen);
    $whereClauses[] = self::toegangTotContactenVanGroep($type, $tables, $whereTables, $contactID, $where, $toegang_tot_groep);
    $whereClause = "";
    foreach($whereClauses as $clause) {
      if (strlen($clause)) {
        if (strlen($whereClause)) {
          $whereClause .= " AND ";
        }
        $whereClause .= " (".$clause.") ";
      }
    }

    $additinalClauses = self::toegangTotGeoContacten($type, $tables, $whereTables, $contactID, $where, $toegang_tot_afdelingen);
    if (!empty($additinalClauses)) {
      if (strlen($whereClause)) {
        $whereClause = " ((" . $whereClause . ") OR (".$additinalClauses."))";
      } else {
        $whereClause = " (".$additinalClauses.")";
      }
    }

    if (strlen($whereClause)) {
      if (strlen($where)) {
        $where .= " AND ";
      }
      $where .= " (" . $whereClause . ") ";
    }
  }

  protected static function toegangTotGeoContacten( $type, &$tables, &$whereTables, &$contactID, &$where, $permissioned_to_contacts) {
    //bepaal toegang tot provincie, regio en of afdeling contact en alle onderliggende contacten
    $ids = $permissioned_to_contacts;
    $ids = $ids + self::retrieveRegiosFromProvincies($ids);
    $ids = $ids + self::retrieveAfdelingenFromRegios($ids);

    if (empty($ids)) {
      return "";
    }

    $ids = implode(", ", $ids);
    return "contact_a.id IN ({$ids})";
  }

  protected static function retrieveRegiosFromProvincies($cids) {
    if (empty($cids)) {
      return array();
    }
    $config = CRM_Geostelsel_Config::singleton();
    $contact_ids = array();
    $sql = "SELECT contact_id_a
            FROM civicrm_relationship
            WHERE contact_id_b IN (".implode(",", $cids).")
            AND relationship_type_id = %1
            AND is_active = 1
            AND (start_date <= NOW() OR start_date IS NULL)
            AND (end_date >= NOW() OR end_date IS NULL)
            ";
    $params[1] = array($config->getProvincieRelationshipTypeId(), 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      $contact_ids[] = $dao->contact_id_a;
    }
    return $contact_ids;
  }

  protected static function retrieveAfdelingenFromRegios($cids) {
    if (empty($cids)) {
      return array();
    }
    $config = CRM_Geostelsel_Config::singleton();
    $contact_ids = array();
    $sql = "SELECT contact_id_a
            FROM civicrm_relationship
            WHERE contact_id_b IN (".implode(",", $cids).")
            AND relationship_type_id = %1
            AND is_active = 1
            AND (start_date <= NOW() OR start_date IS NULL)
            AND (end_date >= NOW() OR end_date IS NULL)
            ";
    $params[1] = array($config->getRegioRelationshipTypeId(), 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      $contact_ids[] = $dao->contact_id_a;
    }
    return $contact_ids;
  }

  protected static function toegangTotContactenVanAfdeling( $type, &$tables, &$whereTables, &$contactID, &$where, $permissioned_to_contacts) {
    $config = CRM_Geostelsel_Config::singleton();
    if (count($permissioned_to_contacts) == 0) {
      return "";
    }

    if ($type != CRM_Core_Permission::VIEW) {
      return "";
    }

    $table = $config->getGeostelselCustomGroup('table_name');
    $afdeling = $config->getAfdelingsField('column_name');
    $regio = $config->getRegioField('column_name');
    $provincie = $config->getProvincieField('column_name');
    $tables['geostelsel'] = $whereTables['geostelsel'] = "LEFT JOIN {$table} geostelsel ON contact_a.id = geostelsel.entity_id";
    $ids = implode(", ", $permissioned_to_contacts);

    //add active membership
    $membership_type = CRM_Geostelsel_Config_MembershipTypes::singleton();
    $membership_table = 'civicrm_membership';
    $tables['membership_access'] = $whereTables['membership_access'] = "LEFT JOIN {$membership_table} membership_access ON contact_a.id = membership_access.contact_id";
    $mtype_ids = implode(", ", $membership_type->getMembershipTypeIds());
    $mstatus_ids = implode(", ", $membership_type->getStatusIds());


    return
      "(
        (
          geostelsel.`{$afdeling}` IN ({$ids})
          OR geostelsel.`{$regio}` IN ({$ids})
          OR geostelsel.`{$provincie}` IN ({$ids})
        )
        AND
        (
          membership_access.membership_type_id IN ({$mtype_ids})
          AND membership_access.status_id IN ({$mstatus_ids})
        )
      )";
  }

  protected static function toegangTotContactenVanGroep( $type, &$tables, &$whereTables, &$contactID, &$where, $permissioned_to_groups) {
    if (count($permissioned_to_groups) == 0) {
      return "";
    }

    if ($type != CRM_Core_Permission::VIEW) {
      return "";
    }

    $ids = implode(", ", $permissioned_to_groups);
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_group where id IN (".$ids.")");
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

}