<?php
// De functies om de clause zelf te genereren op basis van alle geostelsel-informatie zijn voorlopig hier gebleven. De feitelijke aanroep en de beslissing wanneer er iets mee gedaan wordt vindt nu plaats in nl.sp.accesscontrol.

class CRM_Geostelsel_AclGenerator {

  public static function generateWhereClause( $type, &$tables, &$whereTables, &$contactID, &$where ) {
    if ( ! $contactID ) {
      return;
    }

    $session = CRM_Core_Session::singleton();
    $contacts = $session->get('acl_contacts', 'nl.sp.accesscontrol');
    if (!$contacts) {
      $aclCacheTables = array();
      $aclWhereTables = array();
      $aclCacheWhere = "";
      CRM_Geostelsel_BAO_Toegangsgegevens::generateAclWhere($aclCacheTables, $aclWhereTables, $contactID, $aclCacheWhere);
      $aclCacheFrom = CRM_Contact_BAO_Query::fromClause($aclWhereTables);

      $sql = "SELECT contact_a.id as contact_id
            {$aclCacheFrom}
            WHERE 1
            AND {$aclCacheWhere}";

      $dao = CRM_Core_DAO::executeQuery($sql);
      $contacts = array();
      while($dao->fetch()) {
        $contacts[] = $dao->contact_id;
      }
      $session->set('acl_contacts', serialize($contacts), 'nl.sp.accesscontrol');
    } else {
      $contacts = unserialize($contacts);
    }
    if (count($contacts) > 0) {
      $where .= " OR (contact_a.id IN (" . implode(", ", $contacts)."))";
    }


  }

}