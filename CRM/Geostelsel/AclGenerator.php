<?php
// De functies om de clause zelf te genereren op basis van alle geostelsel-informatie zijn voorlopig hier gebleven. De feitelijke aanroep en de beslissing wanneer er iets mee gedaan wordt vindt nu plaats in nl.sp.accesscontrol.

class CRM_Geostelsel_AclGenerator {

  public static function generateWhereClause( $type, &$tables, &$whereTables, &$contactID, &$where ) {
    if ( ! $contactID ) {
      return;
    }

    /**
     * Add contacts a user is allowed to see to the session.
     * So that the ACL queries performans fatser by only doing an 'IN" on contact_id
     * rather than complicated joins and wheres.
     *
     * We store all ids of contacts a user is allowed to see in the session.      *
     * If the data in the session is more than 8 hours old rebuild the data.
     *
     */
    $session = CRM_Core_Session::singleton();
    $contacts = $session->get('acl_contacts', 'nl.sp.accesscontrol');
    $timestamp = $session->get('acl_contacts_timestamp', 'nl.sp.accesscontrol');
    $maxTimestamp = 8*60*60; //8 hours
    $reset = false;
    if ($timestamp && (time()-$timestamp) > $maxTimestamp) {
      $reset = true;
    }
    if (!$contacts || $reset) {
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
      $session->set('acl_contacts_timestamp', time(), 'nl.sp.accesscontrol');
    } else {
      $contacts = unserialize($contacts);
    }
    if (count($contacts) > 0) {
      $where .= " OR (contact_a.id IN (" . implode(", ", $contacts)."))";
    }


  }

}