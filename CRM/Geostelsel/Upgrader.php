<?php

/**
 * Collection of upgrade steps
 */
class CRM_Geostelsel_Upgrader extends CRM_Geostelsel_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    $this->executeCustomDataFile('xml/geostelsel.xml');
    $this->executeCustomDataFile('xml/toegang.xml');
    $this->executeCustomDataFile('xml/gemeente.xml');
    $this->executeCustomDataFile('xml/groep.xml');
    return true;
  }

  public function upgrade_1002() {
    //get all custom fields which are linked to the gemeente lijst
    //and update the value so it includes the province name (same as label)
    $custom_fields = CRM_Core_DAO::executeQuery("SELECT `civicrm_custom_field`.*, `civicrm_custom_group`.`table_name` FROM `civicrm_custom_field` INNER JOIN `civicrm_custom_group` ON `civicrm_custom_field`.`custom_group_id` = `civicrm_custom_group`.`id` INNER JOIN `civicrm_option_group` ON `civicrm_custom_field`.`option_group_id` = `civicrm_option_group`.`id` WHERE `civicrm_option_group`.`name` = 'gemeente'");
    while($custom_fields->fetch()) {
      $sql = "UPDATE `".$custom_fields->table_name."` `e`
              INNER JOIN `civicrm_option_value` `ov`
              ON `ov`.`option_group_id` = %1
              AND `ov`.`value` = `e`.`".$custom_fields->column_name."`
              SET `e`.`".$custom_fields->column_name."` = `ov`.`label`;";
      $params = array();
      $params[1] = array($custom_fields->option_group_id, 'Integer');
      CRM_Core_DAO::executeQuery($sql, $params);
    }

    //update the option group Gemeente
    $sql = "UPDATE `civicrm_option_value` `ov`
            INNER JOIN `civicrm_option_group` `og` ON `ov`.`option_group_id` = `og`.`id`
            SET `ov`.`value` = `ov`.`label`
            WHERE `og`.`name` = 'gemeente'";
    CRM_Core_DAO::executeQuery($sql);

    return true;
  }

  public function upgrade_1003() {
    $this->executeCustomDataFile('xml/toegang.xml');
    $config = CRM_Geostelsel_Config::singleton();
    $params['is_active'] = 1;
    $params['is_required'] = 0;
    $params['is_searchable'] = 1;
    $params['id'] = $config->getPermissionField('id');
    civicrm_api3('CustomField', 'create', $params);
    return true;
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   */
  public function uninstall() {
    return true;
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }*/

  
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001');
    $this->executeCustomDataFile('xml/groep.xml');
    return TRUE;
  }


  /**
   * Example: Run an external SQL script
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
