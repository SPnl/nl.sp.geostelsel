<?php

class CRM_Geostelsel_Config_Toegang {

  private static $singleton;

  private $toegang_custom_group;

  private $toegang_afdeling_custom_field;

  private $toegang_groep_custom_field;

  private $type_custom_field;

  private $link_custom_field;

  private function __construct() {
    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $this->toegang_custom_group = $cfsp->getGroupByName('Toegangsgegevens');
    $this->toegang_afdeling_custom_field = $cfsp->getField('Toegangsgegevens', 'Toegang_tot_contacten_van');
    $this->toegang_groep_custom_field = $cfsp->getField('Toegangsgegevens', 'group_id');
    $this->type_custom_field = $cfsp->getField('Toegangsgegevens', 'type');
    $this->link_custom_field = $cfsp->getField('Toegangsgegevens', 'link');
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
	
	public static function accessToToegangsgegevensCustomGroup() {
		try {
			$accessToCustomGroup = civicrm_api3('CustomGroup', 'getsingle', array('check_permissions' => 1, 'name' => 'Toegangsgegevens'));
			return true;
		} catch (Exception $e) {
			return false;
		}
		return false;
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

  public function getLinkCustomField($key='id') {
    return $this->link_custom_field[$key];
  }

  public function getTypeCustomField($key='id') {
    return $this->type_custom_field[$key];
  }
}