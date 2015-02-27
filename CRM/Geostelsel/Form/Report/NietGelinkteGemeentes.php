<?php

class CRM_Geostelsel_Form_Report_NietGelinkteGemeentes extends CRM_Report_Form {

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
        $this->assign('reportTitle', ts('Gekoppelde gemeentes'));
        parent::preProcess();
    }

    function buildQuery($applyLimit = TRUE) {
        $config = CRM_Geostelsel_Config::singleton();

        return "SELECT `ov`.`label` as `gemeente`, `c`.`display_name` AS `afdeling`
                FROM `civicrm_option_value` `ov`
                LEFT OUTER JOIN `".$config->getGemeenteCustomGroup('table_name')."` `afd_gem` ON `ov`.`value` = `afd_gem`.`".$config->getGemeenteCustomField('column_name')."`
                LEFT OUTER JOIN `civicrm_contact` `c` ON `c`.`id` = `afd_gem`.`entity_id`
                WHERE `ov`.`option_group_id` = '".$config->getGemeenteCustomField('option_group_id')."'
                ORDER BY `afdeling`, `gemeente`;";
    }

    function modifyColumnHeaders() {
        // use this method to modify $this->_columnHeaders
        $this->_columnHeaders['gemeente'] = array('title' => 'Gemeente');
        $this->_columnHeaders['afdeling'] = array('title' =>'Afdeling');
    }

}