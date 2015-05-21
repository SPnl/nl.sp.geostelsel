<?php

class CRM_Geostelsel_Config_Toegang {

  private static $singleton;

  private $toegang_custom_group;

  private $toegang_afdeling_custom_field;

  private $toegang_groep_custom_field;

  private function __construct() {
    $this->toegang_custom_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Toegangsgegevens'));
    $this->toegang_afdeling_custom_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'Toegang_tot_contacten_van', 'custom_group_id' => $this->toegang_custom_group['id']));
    $this->toegang_groep_custom_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'group_id', 'custom_group_id' => $this->toegang_custom_group['id']));
  }

  /**
   * @return \CRM_Geostelsel_Config_Toegang
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Geostelsel_Config_Toegang();
    }
    return self::$singleton;
  }

  public function getToegangCustomGroup($key='id') {
    return $this->toegang_custom_group[$key];
  }

  public function getToegangAfdelingCustomField($key='id') {
    return $this->toegang_afdeling_custom_field[$key];
  }

  public function getToegangGroepCustomField($key='id') {
    return $this->toegang_groep_custom_field[$key];
  }

}