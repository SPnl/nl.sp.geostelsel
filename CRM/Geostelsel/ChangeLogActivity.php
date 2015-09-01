<?php

class CRM_Geostelsel_ChangeLogActivity {

  private static $singleton;

  private function __construct() {

  }

  public static function singleton() {
    if (!isset(self::$singleton)) {
      self::$singleton = new CRM_Geostelsel_ChangeLogActivity();
    }
    return self::$singleton;
  }

  public function custom($op,$groupID, $entityID, &$params ) {
    $config = CRM_Geostelsel_Config::singleton();
    if ($groupID != $config->getGeostelselCustomGroup('id')) {
      return;
    }
  }

  public function logNewAfdeling($contact_id, $afdeling_id) {

  }

}