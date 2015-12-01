<?php

class CRM_Geostelsel_Groep_ParentGroup {
  
  /**
   * Return the parent groups to which this contact has access to
   * 
   * The return value is based on the groep field of the afdeling/regio/provincie
   * to which this user has access to
   * 
   * @param array $contactID
   */
  public function getParentGroupsByContact($contactID) {
    $return = $parents = $this->accessToGroups($contactID);

    if (count($parents)) {
      $subgroups = CRM_Contact_BAO_GroupNesting::getChildGroupIds($parents);
      $parents = CRM_Contact_BAO_GroupNesting::getParentGroupIds($parents);
      foreach ($subgroups as $sub_gid) {
        $return[] = $sub_gid;
      };
      foreach ($parents as $p_gid) {
        $return[] = $p_gid;
      }
    }
    
    return $return;
  }
  
  public function accessToGroups($contactID) {
    $geo_config = CRM_Geostelsel_Config::singleton();
    $group_config = CRM_Geostelsel_Groep_Config::singleton();
    
    $access_table = $geo_config->getPermissionTable('table_name');
    $access_field = $geo_config->getPermissionField('column_name');
    $group_table = $group_config->getGroepCustomGroup('table_name');
    $group_field = $group_config->getGroepCustomField('column_name');
    $sql = "SELECT `{$group_table}`.`{$group_field}` AS `group_id` FROM `{$access_table}` INNER JOIN `{$group_table}` ON `{$access_table}`.`{$access_field}` = `{$group_table}`.`entity_id`  WHERE `{$access_table}`.`entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($contactID, 'Integer')));
    
    $return = array();
    while($dao->fetch()) {
      $return[] = $dao->group_id;
    }
    
    return $return;
  }

  public function parentGroups() {
    $group_config = CRM_Geostelsel_Groep_Config::singleton();
    $group_table = $group_config->getGroepCustomGroup('table_name');
    $group_field = $group_config->getGroepCustomField('column_name');

    $sql = "SELECT `{$group_table}`.`{$group_field}` AS `group_id` FROM `{$group_table}` INNER JOIN `civicrm_contact` as contact_a ON `{$group_table}`.`entity_id` = `contact_a`.`id`  WHERE contact_a.is_deleted = '0' and `{$group_table}`.`{$group_field}` is not null";
    $dao = CRM_Core_DAO::executeQuery($sql);

    $return = array();
    while($dao->fetch()) {
      $return[] = $dao->group_id;
    }

    return $return;
  }
  
}

