<?php

class CRM_Geostelsel_CiviMail_FromMailAddresses {

  /**
   * Change the option list with from values from the Afdelingen, Regio's, en provincies
   *
   * @param $options
   * @param $name
   */
  public static function optionValues(&$options, $name) {
    if ($name != 'from_email_address') {
      return;
    }
    if (!CRM_Core_Permission::check('civimail use default from addresses')) {
      //unset default options
      $options = array();
    }

    self::getFromContacts($options);
  }

  protected static function getFromContacts(&$options) {
    $sep = CRM_Core_DAO::VALUE_SEPARATOR;
    list($aclFrom, $aclWhere) = CRM_Contact_BAO_Contact_Permission::cacheClause('contact_a');
    $sql = "SELECT contact_a.id as contact_id, contact_a.display_name, e.email
            FROM civicrm_contact contact_a
            INNER JOIN civicrm_email e on contact_a.id = e.contact_id AND e.is_primary = 1
            {$aclFrom}
            WHERE (
              contact_a.contact_sub_type LIKE '%{$sep}SP_Afdeling{$sep}%'
              OR contact_a.contact_sub_type LIKE '%{$sep}SP_Regio{$sep}%'
              OR contact_a.contact_sub_type LIKE '%{$sep}SP_Provincie{$sep}%'
               OR contact_a.contact_sub_type LIKE '%{$sep}SP_Werkgroep{$sep}%'
              )
            AND {$aclWhere}
            ORDER BY contact_a.sort_name";

    $dao = CRM_Core_DAO::executeQuery($sql);

    while($dao->fetch()) {
      $options['contact_id_'.$dao->contact_id] = '"'.$dao->display_name. '" <'.$dao->email.'>';
    }
  }

}