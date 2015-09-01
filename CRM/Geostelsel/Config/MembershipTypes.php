<?php

class CRM_Geostelsel_Config_MembershipTypes {

    private static $singleton;

    private $membership_type_ids = array();

    private $status_ids = array();

    private $decaesed_status_id;

    private function __construct() {
        $membership_types = array(
            'Lid SP',
            'Lid SP en ROOD',
            'Lid ROOD',
        );
        $sql = "SELECT id from civicrm_membership_type where name = %1";
        foreach($membership_types as $type) {
            $params = array(
                1 => array($type, 'String'),
            );
            $this->membership_type_ids[] = CRM_Core_DAO::singleValueQuery($sql, $params);
        }

        $statusses = array(
          'New',
          'Current',
          'Grace',
        );
        $sql = "SELECT id from civicrm_membership_status where name = %1";
        foreach($statusses as $status) {
          $params = array(
            1 => array($status, 'String'),
          );
          $this->status_ids[] = CRM_Core_DAO::singleValueQuery($sql, $params);
        }

        $sql = "SELECT id from civicrm_membership_status where name = %1";
        $this->decaesed_status_id = CRM_Core_DAO::singleValueQuery($sql, array(1 => array('Deceased', 'String')));
    }

    /**
     * @return CRM_Geostelsel_Config_MembershipTypes
     */
    public static function singleton() {
        if (!self::$singleton) {
            self::$singleton = new CRM_Geostelsel_Config_MembershipTypes();
        }
        return self::$singleton;
    }

    public function getMembershipTypeIds() {
        return $this->membership_type_ids;
    }

    public function getStatusIds() {
      return $this->status_ids;
    }

    public function getDeceasedStatusId() {
        return $this->decaesed_status_id;
    }

}