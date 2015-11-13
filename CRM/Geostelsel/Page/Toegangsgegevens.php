<?php

class CRM_Geostelsel_Page_Toegangsgegevens extends CRM_Core_Page_Basic {

  function getBAOName() {
    return 'CRM_Geostelsel_BAO_Toegangsgegevens';
  }

  function userContext($mode = NULL) {
    return 'civicrm/toegangsgegevens/toegangsgegevens';
  }

  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        CRM_Core_Action::UPDATE  => array(
          'name'  => ts('Edit'),
          'url'   => 'civicrm/toegangsgegevens/toegangsgegevens',
          'qs'    => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Toegangsgegevens'),
        ),
        CRM_Core_Action::DELETE  => array(
          'name'  => ts('Delete'),
          'url'   => 'civicrm/toegangsgegevens/toegangsgegevens',
          'qs'    => 'action=delete&id=%%id%%',
          'title' => ts('Delete Toegangsgegevens'),
        ),
      );
    }
    return self::$_links;
  }

  /**
   * name of the edit form class
   *
   * @return string
   * @access public
   */
  function editForm() {
    return 'CRM_Geostelsel_Form_Toegangsgegevens';
  }

  /**
   * name of the form
   *
   * @return string
   * @access public
   */
  function editName() {
    return 'CRM_Geostelsel_Form_Toegangsgegevens';
  }

  function browse() {
    $n = func_num_args();
    $action = ($n > 0) ? func_get_arg(0) : NULL;
    $links = &$this->links();
    if ($action == NULL) {
      if (!empty($links)) {
        $action = array_sum(array_keys($links));
      }
    }

    $values = array();

    $this->assign('rows', $values);
  }

}