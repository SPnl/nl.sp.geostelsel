<?php

class CRM_Geostelsel_Form_Report_VerkeerdgekoppeldeGeo extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE;
  protected $_add2groupSupported = FALSE;

  protected $_noFields = TRUE;

  function __construct() {
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    $this->_columns = array();
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Geo info verkeerd gekoppeld'));
    parent::preProcess();
  }

  function buildQuery($applyLimit = TRUE) {
    $config = CRM_Geostelsel_Config::singleton();

    if ($applyLimit) {
      $this->limit();
    }

    $sql = "SELECT
        c.id as contact_id,
        c.display_name as contact_name,
        cbs.`".$config->getPostcodeGemeenteCustomField('column_name')."` AS `woonplaats_gemeente`,
        `afdeling`.`id` AS `afdeling_id`,
        `afdeling`.`display_name` AS `afdeling_display_name`,
        `gemeente`.`".$config->getGemeenteCustomField('column_name')."` AS `afdelings_gemeente`,
        `geo_afdeling`.`id` AS `geo_afdeling_id`,
        `geo_afdeling`.`display_name` AS `geo_afdeling_display_name`,
        `geo`.`".$config->getHandmatigeInvoerField('column_name')."` AS `manual_entry`
        FROM `civicrm_address` `a`
        INNER JOIN `civicrm_contact` `c` ON `a`.contact_id = c.id
        LEFT JOIN `".$config->getPostcodeCustomGroup('table_name')."` `cbs` ON `cbs`.`entity_id` = `a`.`id`
        LEFT JOIN `".$config->getGemeenteCustomGroup('table_name')."` `gemeente` ON `gemeente`.`".$config->getGemeenteCustomField('column_name')."` LIKE `cbs`.`".$config->getPostcodeGemeenteCustomField('column_name')."`
        LEFT JOIN `civicrm_contact` `afdeling` ON `gemeente`.`entity_id` = `afdeling`.`id`
        LEFT JOIN `".$config->getGeostelselCustomGroup('table_name')."` `geo` ON `c`.id = `geo`.`entity_id`
        LEFT JOIN `civicrm_contact` `geo_afdeling` ON `geo_afdeling`.`id` = `geo`.`".$config->getAfdelingsField('column_name')."`
        WHERE `a`.is_primary = 1 AND `afdeling`.`id` != `geo_afdeling`.`id`
        ORDER BY `woonplaats_gemeente` ASC, `afdeling_display_name` ASC, `geo_afdeling_display_name` ASC
        {$this->_limit}";

    return $sql;
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function modifyColumnHeaders() {
    // use this method to modify $this->_columnHeaders
    $this->_columnHeaders['contact_id'] = array('title' => 'Contact ID');
    $this->_columnHeaders['contact_name'] = array('title' =>'Name');
    $this->_columnHeaders['woonplaats_gemeente'] = array('title' =>'Woonplaats (Gemeneet)');
    $this->_columnHeaders['afdeling_display_name'] = array('title' =>'Afdeling (volgens postcode db)');
    $this->_columnHeaders['geo_afdeling_display_name'] = array('title' =>'Gekoppelde afdeling');
    $this->_columnHeaders['manual_entry'] = array('title' =>'Handmatige invoer');
  }

  function alterDisplay(&$rows) {
    foreach($rows as $rowNum => $row) {
      $url = CRM_Utils_System::url("civicrm/contact/view",
        'reset=1&cid=' . $row['contact_id'],
        $this->_absoluteUrl
      );
      $rows[$rowNum]['contact_name_link'] = $url;

      $url = CRM_Utils_System::url("civicrm/contact/view",
        'reset=1&cid=' . $row['afdeling_id'],
        $this->_absoluteUrl
      );
      $rows[$rowNum]['afdeling_display_name_link'] = $url;

      $url = CRM_Utils_System::url("civicrm/contact/view",
        'reset=1&cid=' . $row['geo_afdeling_id'],
        $this->_absoluteUrl
      );
      $rows[$rowNum]['geo_afdeling_display_name_link'] = $url;

    }
  }

}