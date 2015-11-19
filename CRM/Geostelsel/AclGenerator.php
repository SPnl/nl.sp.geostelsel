<?php
// De functies om de clause zelf te genereren op basis van alle geostelsel-informatie zijn voorlopig hier gebleven. De feitelijke aanroep en de beslissing wanneer er iets mee gedaan wordt vindt nu plaats in nl.sp.accesscontrol.

class CRM_Geostelsel_AclGenerator {

  public static function generateWhereClause( $type, &$tables, &$whereTables, &$contactID, &$where ) {
    if ( ! $contactID ) {
      return;
    }

    CRM_Geostelsel_BAO_Toegangsgegevens::generateAclWhere($tables, $whereTables, $contactID, $where);
  }

}