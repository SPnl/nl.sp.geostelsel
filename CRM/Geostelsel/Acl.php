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

    self::toegangTotContactenVanAfdeling($type, $tables, $whereTables, $contactID, $where, $toegang_tot_afdelingen);
    self::toegangTotContactenVanGroep($type, $tables, $whereTables, $contactID, $where, $toegang_tot_groep);
  }

  protected static function toegangTotContactenVanAfdeling( $type, &$tables, &$whereTables, &$contactID, &$where, $permissioned_to_contacts) {
    $config = CRM_Geostelsel_Config::singleton();
    if (count($permissioned_to_contacts) == 0) {
      return;
    }

    if ($type != CRM_Core_Permission::VIEW) {
      return;
    }

    $table = $config->getGeostelselCustomGroup('table_name');
    $afdeling = $config->getAfdelingsField('column_name');
    $regio = $config->getRegioField('column_name');
    $provincie = $config->getProvincieField('column_name');
    $tables[$table] = $whereTables[$table] = "LEFT JOIN {$table} geostelsel ON contact_a.id = geostelsel.entity_id";
    $ids = implode(", ", $permissioned_to_contacts);

    //add active membership
    $membership_type = CRM_Geostelsel_Config_MembershipTypes::singleton();
    $membership_table = 'civicrm_membership';
    $tables[$membership_table] = $whereTables[$membership_table] = "LEFT JOIN {$membership_table} membership_access ON contact_a.id = membership_access.contact_id";
    $mtype_ids = implode(", ", $membership_type->getMembershipTypeIds());
    $mstatus_ids = implode(", ", $membership_type->getStatusIds());


    $whereClause = " (
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

    if (!empty($where)) {
      $where .= " OR";
    }
    $where .= $whereClause;
  }

  protected static function toegangTotContactenVanGroep( $type, &$tables, &$whereTables, &$contactID, &$where, $permissioned_to_groups) {
    if (count($permissioned_to_groups) == 0) {
      return;
    }

    if ($type != CRM_Core_Permission::VIEW) {
      return;
    }

    $table = 'civicrm_group_contact';
    $tables[$table] = $whereTables[$table] = "LEFT JOIN {$table} toegang_group ON contact_a.id = toegang_group.contact_id AND toegang_group.status = 'Added'";
    $ids = implode(", ", $permissioned_to_groups);

    $whereClause = " (`toegang_group`.`group_id` IN ({$ids}))";

    if (!empty($where)) {
      $where .= " OR";
    }
    $where .= $whereClause;
  }

}